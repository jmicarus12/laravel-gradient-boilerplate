<?php

use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Areas
|--------------------------------------------------------------------------
|
| An area is a part of the app with its own kind of account, its own login
| and its own session. Everything about an area is configured here.
|
|   guard     Auth guard from config/auth.php (which table its accounts live in)
|   theme     Folder in resources/themes/
|   prefix    URL prefix for its routes
|   routes    Folder whose *.php files are loaded for this area. Every route in
|             it requires a logged-in, active account of the area's guard.
|             Its "public" subfolder holds routes without that check (login).
|             (The "web" area uses routes/web.php instead.)
|   home      Route name of the area's start page (also where logged-in accounts
|             are sent from guest-only pages like login)
|   login     Route name guests are sent to
|   register  Route name of the sign-up page (optional)
|   account   Route name of the "my account / profile" page (optional)
|   logout    Route name of the POST logout route
|   session   Overrides for config/session.php — own cookie, own table,
|             own lifetime. Empty = use config/session.php as is.
|
| Adding an area: see docs/adding-an-area.md (step 11).
|
*/

$app = Str::slug((string) env('APP_NAME', 'laravel'));

return [

    'default' => 'web',

    'areas' => [

        'web' => [
            'guard' => 'web',
            'theme' => env('THEME_WEB', 'gradient-able'),
            'home' => 'home',
            'login' => 'login',
            'register' => 'register',
            'account' => 'profile.edit',
            'logout' => 'logout',
            'session' => [],
        ],

        'admin' => [
            'guard' => 'admin',
            'theme' => env('THEME_ADMIN', 'gradient-able'),
            'prefix' => 'admin',
            'routes' => 'routes/admin',
            'home' => 'admin.dashboard',
            'login' => 'admin.login',
            'account' => 'admin.account.edit',
            'logout' => 'admin.logout',
            'session' => [
                'cookie' => env('ADMIN_SESSION_COOKIE', "{$app}-admin-session"),
                'table' => 'admin_sessions',
                'lifetime' => (int) env('ADMIN_SESSION_LIFETIME', 30),
                'expire_on_close' => false,
            ],
        ],

        'client' => [
            'guard' => 'client',
            'theme' => env('THEME_CLIENT', 'gradient-able'),
            'prefix' => 'client',
            'routes' => 'routes/client',
            'home' => 'client.dashboard',
            'login' => 'client.login',
            'account' => 'client.account.edit',
            'logout' => 'client.logout',
            'session' => [
                'cookie' => env('CLIENT_SESSION_COOKIE', "{$app}-client-session"),
                'table' => 'client_sessions',
                'lifetime' => (int) env('CLIENT_SESSION_LIFETIME', 30),
                'expire_on_close' => false,
            ],
        ],

    ],

];
