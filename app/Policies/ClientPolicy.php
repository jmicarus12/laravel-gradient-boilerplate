<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Admin;
use App\Models\Client;

/**
 * Registered for spatie's Client model in AppServiceProvider (it lives outside App\Models,
 * so Laravel can't guess it).
 */
class ClientPolicy
{
    public function viewAny(Admin $actor): bool
    {
        return $actor->can(Permission::ClientsView);
    }

    public function view(Admin $actor, Client $client): bool
    {
        return $actor->can(Permission::ClientsView);
    }

    public function create(Admin $actor): bool
    {
        return $actor->can(Permission::ClientsCreate);
    }

    public function update(Admin $actor, Client $client): bool
    {
        return $actor->can(Permission::ClientsUpdate);
    }

    public function delete(Admin $actor, Client $client): bool
    {
        return $actor->can(Permission::ClientsDelete);
    }

    public function deactivate(Admin $actor, Client $client): bool
    {
        return $actor->can(Permission::ClientsDeactivate);
    }
}
