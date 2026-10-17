<?php

use App\Enums\Permission;

/*
|--------------------------------------------------------------------------
| Navigation menus
|--------------------------------------------------------------------------
|
| Menus live here, not in the theme, so every theme renders the same items.
|
| Item keys:
|   caption   Section heading (no link)
|   label     Text shown
|   route     Route name; items whose route doesn't exist yet are hidden
|   active    Route pattern that marks the item active (defaults to route)
|   icon      Phosphor icon name without "ph-" (https://phosphoricons.com)
|   can       Permission needed to see the item (App\Enums\Permission)
|   children  Nested items (same keys)
|
| "pinned" menus stick to the bottom of the sidebar (system/admin tools),
| while the main menu above them scrolls.
|
*/

return [

    'admin' => [
        ['caption' => 'Navigation'],
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'gauge'],
        ['label' => 'Clients', 'route' => 'admin.clients.index', 'icon' => 'address-book'],

        // Your modules go here (e.g. ['caption' => 'Human Resources'], ...)

        ['caption' => 'Theme'],
        ['label' => 'UI Components', 'route' => 'admin.components', 'icon' => 'palette'],
        ['label' => 'Icons', 'route' => 'admin.icons', 'icon' => 'shapes'],
    ],

    'web' => [
        ['label' => 'Home', 'route' => 'home', 'icon' => 'house'],
    ],

    'pinned' => [

        'admin' => [
            ['caption' => 'System'],
            ['label' => 'Access', 'icon' => 'lock-key', 'children' => [
                ['label' => 'Admins', 'route' => 'admin.admins.index', 'active' => 'admin.admins.*', 'can' => Permission::AdminsView],
                ['label' => 'Roles', 'route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'can' => Permission::RolesView],
            ]],
            ['label' => 'Activity Log', 'route' => 'admin.activity.index', 'icon' => 'clock-counter-clockwise', 'can' => Permission::ActivityView],
            ['label' => 'System Logs', 'route' => 'log-viewer.index', 'icon' => 'file-text', 'can' => 'viewSystemLogs'], // Super Admin only
        ],

    ],

];
