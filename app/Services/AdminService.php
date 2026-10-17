<?php

namespace App\Services;

use App\Exceptions\ActionNotAllowed;
use App\Models\Admin;
use App\Services\Concerns\RecordsAdminActivity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Admin account rules that go beyond "does this admin have the permission?"
 * (that part lives in AdminPolicy). Every method gets the acting admin, so it
 * can stop people from locking themselves out or giving themselves more power.
 */
class AdminService
{
    use RecordsAdminActivity;

    /**
     * Roles the actor may hand out: Super Admins → all; everyone else → only roles
     * whose permissions they already have themselves (no privilege escalation).
     *
     * @return Collection<int, Role>
     */
    public function assignableRoles(Admin $actor): Collection
    {
        $roles = Role::where('guard_name', 'admin')->with('permissions')->orderBy('name')->get();

        if ($actor->isSuperAdmin()) {
            return $roles;
        }

        $own = $actor->getAllPermissions()->pluck('name');

        return $roles
            ->reject(fn (Role $role) => $role->name === Admin::SUPER_ADMIN)
            ->filter(fn (Role $role) => $role->permissions->pluck('name')->diff($own)->isEmpty())
            ->values();
    }

    /**
     * @param  array{name: string, email: string, password: string, active?: bool}  $data
     * @param  list<string>  $roles
     */
    public function create(Admin $actor, array $data, array $roles): Admin
    {
        $this->ensureCanAssign($actor, $roles);

        return DB::transaction(function () use ($actor, $data, $roles) {
            $admin = Admin::create($data);
            $admin->syncRoles($roles);

            $this->record($actor, 'created', "Created admin {$admin->name}", $admin, [
                'email' => $admin->email,
                'roles' => $roles,
            ]);

            return $admin;
        });
    }

    /**
     * @param  array{name: string, email: string, active?: bool}  $data
     * @param  list<string>  $roles
     */
    public function update(Admin $actor, Admin $admin, array $data, array $roles): Admin
    {
        $current = $admin->getRoleNames()->sort()->values()->all();
        $wanted = collect($roles)->sort()->values()->all();

        if ($current !== $wanted) {
            if ($actor->is($admin)) {
                throw new ActionNotAllowed(__("You can't change your own roles."));
            }

            // Removing roles the actor couldn't have assigned is escalation too.
            $this->ensureCanAssign($actor, array_merge($roles, array_diff($current, $roles)));
        }

        if ($actor->is($admin) && array_key_exists('active', $data) && ! $data['active']) {
            throw new ActionNotAllowed(__("You can't deactivate yourself."));
        }

        return DB::transaction(function () use ($actor, $admin, $data, $roles, $current, $wanted) {
            $admin->fill($data);
            $changes = collect($admin->getDirty())
                ->map(fn ($new, $field) => ['from' => $admin->getOriginal($field), 'to' => $new])
                ->all();
            $admin->save();
            $admin->syncRoles($roles);

            if ($current !== $wanted) {
                $changes['roles'] = ['from' => $current, 'to' => $wanted];
            }

            if ($changes !== []) {
                $this->record($actor, 'updated', "Updated admin {$admin->name}", $admin, ['changes' => $changes]);
            }

            return $admin;
        });
    }

    public function changePassword(Admin $actor, Admin $admin, string $password): void
    {
        $admin->forceFill([
            'password' => $password,
            'remember_token' => Str::random(60),
        ])->save();

        $this->record($actor, 'password-changed', "Changed the password of {$admin->name}", $admin);
    }

    public function setActive(Admin $actor, Admin $admin, bool $active): void
    {
        if (! $active && $actor->is($admin)) {
            throw new ActionNotAllowed(__("You can't deactivate yourself."));
        }

        $admin->update(['active' => $active]);

        if (! $active) {
            $this->endSessions($admin);
        }

        $this->record($actor, $active ? 'reactivated' : 'deactivated', ($active ? 'Reactivated' : 'Deactivated')." admin {$admin->name}", $admin);
    }

    public function delete(Admin $actor, Admin $admin): void
    {
        if ($actor->is($admin)) {
            throw new ActionNotAllowed(__("You can't delete your own account."));
        }

        $this->endSessions($admin);
        $admin->delete();

        $this->record($actor, 'deleted', "Deleted admin {$admin->name}", $admin);
    }

    public function restore(Admin $actor, Admin $admin): void
    {
        $admin->restore();

        $this->record($actor, 'restored', "Restored admin {$admin->name}", $admin);
    }

    /** Log the admin out on every device: delete their sessions and rotate "remember me". */
    public function clearSessions(Admin $actor, Admin $admin): int
    {
        if ($actor->is($admin)) {
            throw new ActionNotAllowed(__('Use Logout to end your own session.'));
        }

        $count = $this->endSessions($admin);

        $this->record($actor, 'sessions-cleared', "Logged {$admin->name} out of all devices", $admin, ['sessions' => $count]);

        return $count;
    }

    public function activeSessionCount(Admin $admin): int
    {
        return DB::table(config('areas.areas.admin.session.table'))->where('user_id', $admin->id)->count();
    }

    protected function endSessions(Admin $admin): int
    {
        $admin->forceFill(['remember_token' => Str::random(60)])->saveQuietly();

        return DB::table(config('areas.areas.admin.session.table'))->where('user_id', $admin->id)->delete();
    }

    /**
     * @param  list<string>  $roles
     */
    protected function ensureCanAssign(Admin $actor, array $roles): void
    {
        $allowed = $this->assignableRoles($actor)->pluck('name');

        $denied = collect($roles)->diff($allowed);

        if ($denied->isNotEmpty()) {
            throw new ActionNotAllowed(__("You can't assign the role(s): :roles.", ['roles' => $denied->implode(', ')]));
        }
    }
}
