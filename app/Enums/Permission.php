<?php

namespace App\Enums;

/**
 * Every permission an admin role can be given — the single source of truth.
 *
 * Add a case here, run `php artisan permissions:sync`, and it appears in the
 * role editor. Check it with ->can(Permission::AdminsView) in routes, policies
 * and @can in Blade. Never check role names in code; roles are just bundles
 * of these permissions, managed in the admin UI.
 */
enum Permission: string
{
    // Admin accounts
    case AdminsView = 'admins.view';
    case AdminsCreate = 'admins.create';
    case AdminsUpdate = 'admins.update';
    case AdminsDeactivate = 'admins.deactivate';
    case AdminsDelete = 'admins.delete';
    case AdminsClearSessions = 'admins.clear-sessions';

    // Client accounts
    case ClientsView = 'clients.view';
    case ClientsCreate = 'clients.create';
    case ClientsUpdate = 'clients.update';
    case ClientsDeactivate = 'clients.deactivate';
    case ClientsDelete = 'clients.delete';

    // Roles
    case RolesView = 'roles.view';
    case RolesManage = 'roles.manage';

    // Activity log
    case ActivityView = 'activity.view';

    /** Guard the permissions belong to (config/auth.php). */
    public const GUARD = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::AdminsView => 'View admins',
            self::AdminsCreate => 'Create admins',
            self::AdminsUpdate => 'Edit admins & change their passwords',
            self::AdminsDeactivate => 'Deactivate / reactivate admins',
            self::AdminsDelete => 'Delete / restore admins',
            self::AdminsClearSessions => 'Log admins out of all devices',
            self::RolesView => 'View roles',
            self::RolesManage => 'Create, edit & delete roles',
            self::ActivityView => 'View the activity log',
            self::ClientsView => 'View clients',
            self::ClientsCreate => 'Create clients',
            self::ClientsUpdate => 'Edit clients info',
            self::ClientsDeactivate => 'Deactivate / reactivate clients',
            self::ClientsDelete => 'Delete / restore clients',
        };
    }

    /** Heading the permission is listed under in the role editor. */
    public function group(): string
    {
        return match ($this) {
            self::AdminsView, self::AdminsCreate, self::AdminsUpdate,
            self::AdminsDeactivate, self::AdminsDelete, self::AdminsClearSessions => 'Admins',
            self::ClientsView, self::ClientsCreate, self::ClientsUpdate,
            self::ClientsDeactivate, self::ClientsDelete => 'Clients',
            self::RolesView, self::RolesManage => 'Roles',
            self::ActivityView => 'Activity log',
        };
    }

    /**
     * @return array<string, list<self>>
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::cases() as $permission) {
            $groups[$permission->group()][] = $permission;
        }

        return $groups;
    }
}
