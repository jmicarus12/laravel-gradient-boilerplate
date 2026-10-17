<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Admin;
use Spatie\Permission\Models\Role;

/**
 * Registered for spatie's Role model in AppServiceProvider (it lives outside App\Models,
 * so Laravel can't guess it).
 */
class RolePolicy
{
    public function viewAny(Admin $actor): bool
    {
        return $actor->can(Permission::RolesView);
    }

    public function view(Admin $actor, Role $role): bool
    {
        return $actor->can(Permission::RolesView);
    }

    public function create(Admin $actor): bool
    {
        return $actor->can(Permission::RolesManage);
    }

    public function update(Admin $actor, Role $role): bool
    {
        return $actor->can(Permission::RolesManage);
    }

    public function delete(Admin $actor, Role $role): bool
    {
        return $actor->can(Permission::RolesManage);
    }
}
