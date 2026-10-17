<?php

namespace App\Providers;

use App\Models\Admin;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Opcodes\LogViewer\Facades\LogViewer;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Used by every "password" rule (register, change password, admin forms).
        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(8)->letters()->numbers()->uncompromised()
            : Password::min(8));

        // Super Admins pass every permission and policy check. Returning null (not false)
        // for everyone else lets the normal checks decide.
        Gate::before(fn ($account) => $account instanceof Admin && $account->isSuperAdmin() ? true : null);

        Gate::policy(Role::class, RolePolicy::class);

        // Super Admin only: no one else is ever granted this (Gate::before lets Super Admins through).
        Gate::define('viewSystemLogs', fn () => false);
        LogViewer::auth(fn ($request) => Gate::forUser($request->user('admin'))->allows('viewSystemLogs'));
    }
}
