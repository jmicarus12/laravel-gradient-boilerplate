<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Admin;

/**
 * Who may do what to admin accounts. Laravel finds this policy automatically
 * (App\Models\Admin → App\Policies\AdminPolicy).
 *
 * Super Admins never reach these methods (Gate::before). Business rules such as
 * "you can't delete yourself" live in the admin service (step 9), not here.
 */
class AdminPolicy
{
    public function viewAny(Admin $actor): bool
    {
        return $actor->can(Permission::AdminsView);
    }

    public function view(Admin $actor, Admin $admin): bool
    {
        return $actor->can(Permission::AdminsView);
    }

    public function create(Admin $actor): bool
    {
        return $actor->can(Permission::AdminsCreate);
    }

    public function update(Admin $actor, Admin $admin): bool
    {
        return $actor->can(Permission::AdminsUpdate) && $this->notSuperAdmin($admin);
    }

    public function deactivate(Admin $actor, Admin $admin): bool
    {
        return $actor->can(Permission::AdminsDeactivate) && $this->notSuperAdmin($admin);
    }

    public function delete(Admin $actor, Admin $admin): bool
    {
        return $actor->can(Permission::AdminsDelete) && $this->notSuperAdmin($admin);
    }

    public function restore(Admin $actor, Admin $admin): bool
    {
        return $actor->can(Permission::AdminsDelete);
    }

    public function clearSessions(Admin $actor, Admin $admin): bool
    {
        return $actor->can(Permission::AdminsClearSessions) && $this->notSuperAdmin($admin);
    }

    /** Only Super Admins may change Super Admin accounts. */
    protected function notSuperAdmin(Admin $admin): bool
    {
        return ! $admin->isSuperAdmin();
    }
}
