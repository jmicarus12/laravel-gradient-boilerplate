<?php

namespace App\Services;

use App\Enums\Permission;
use App\Exceptions\ActionNotAllowed;
use App\Models\Admin;
use App\Services\Concerns\RecordsAdminActivity;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Role rules beyond the RolePolicy permission check.
 */
class RoleService
{
    use RecordsAdminActivity;

    /**
     * Permissions the actor may put into a role: their own (Super Admins: all).
     *
     * @return list<string>
     */
    public function grantablePermissions(Admin $actor): array
    {
        $all = array_map(fn (Permission $p) => $p->value, Permission::cases());

        return $actor->isSuperAdmin()
            ? $all
            : array_values(array_intersect($all, $actor->getAllPermissions()->pluck('name')->all()));
    }

    /**
     * @param  list<string>  $permissions
     */
    public function create(Admin $actor, string $name, array $permissions): Role
    {
        $this->ensureNotReserved($name);
        $this->ensureCanGrant($actor, $permissions);

        return DB::transaction(function () use ($actor, $name, $permissions) {
            $role = Role::create(['name' => $name, 'guard_name' => Permission::GUARD])->syncPermissions($permissions);

            $this->record($actor, 'role-created', "Created role {$role->name}", $role, ['permissions' => $permissions]);

            return $role;
        });
    }

    /**
     * @param  list<string>  $permissions
     */
    public function update(Admin $actor, Role $role, string $name, array $permissions): Role
    {
        $this->ensureEditable($role);
        $this->ensureNotReserved($name);

        // Adding or removing a permission the actor doesn't hold would let them reshape
        // access beyond their own — only Super Admins may do that.
        $changed = array_merge(
            array_diff($permissions, $role->permissions->pluck('name')->all()),
            array_diff($role->permissions->pluck('name')->all(), $permissions),
        );
        $this->ensureCanGrant($actor, $changed);

        if ($actor->hasRole($role) && ! $actor->isSuperAdmin()) {
            throw new ActionNotAllowed(__("You can't edit a role you have yourself."));
        }

        return DB::transaction(function () use ($actor, $role, $name, $permissions) {
            $before = $role->permissions->pluck('name')->all();
            $oldName = $role->name;

            $role->update(['name' => $name]);
            $role->syncPermissions($permissions);

            $this->record($actor, 'role-updated', "Updated role {$role->name}", $role, array_filter([
                'renamed' => $oldName !== $name ? ['from' => $oldName, 'to' => $name] : null,
                'added' => array_values(array_diff($permissions, $before)),
                'removed' => array_values(array_diff($before, $permissions)),
            ]));

            return $role;
        });
    }

    public function delete(Admin $actor, Role $role): void
    {
        $this->ensureEditable($role);

        $count = $role->users()->count();
        if ($count > 0) {
            throw new ActionNotAllowed(trans_choice(
                '{1} This role is still assigned to 1 admin. Remove it from them first.|[2,*] This role is still assigned to :count admins. Remove it from them first.',
                $count,
            ));
        }

        $role->delete();

        $this->record($actor, 'role-deleted', "Deleted role {$role->name}", null, ['subject_name' => $role->name]);
    }

    public function isLocked(Role $role): bool
    {
        return $role->name === Admin::SUPER_ADMIN;
    }

    protected function ensureEditable(Role $role): void
    {
        if ($this->isLocked($role)) {
            throw new ActionNotAllowed(__('The :role role is built in and can\'t be changed.', ['role' => Admin::SUPER_ADMIN]));
        }
    }

    protected function ensureNotReserved(string $name): void
    {
        if (strcasecmp(trim($name), Admin::SUPER_ADMIN) === 0) {
            throw new ActionNotAllowed(__('That role name is reserved.'));
        }
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function ensureCanGrant(Admin $actor, array $permissions): void
    {
        $denied = array_diff($permissions, $this->grantablePermissions($actor));

        if ($denied !== []) {
            throw new ActionNotAllowed(__("You can't grant permissions you don't have: :list.", ['list' => implode(', ', $denied)]));
        }
    }
}
