<?php

namespace App\Listeners;

use App\Models\Admin;
use App\Services\Concerns\RecordsAdminActivity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

/**
 * Admin logins, logouts and failed logins → Activity Log.
 * User (web guard) logins are not recorded.
 *
 * No registration needed: Laravel discovers listeners in app/Listeners from the
 * event types in handle(). (Registering it again with Event::listen() would log twice.)
 */
class RecordAdminAuthActivity
{
    use RecordsAdminActivity;

    public function handle(Login|Logout|Failed $event): void
    {
        if ($event->guard !== 'admin') {
            return;
        }

        $ip = request()->ip();

        match (true) {
            $event instanceof Login && $event->user instanceof Admin => $this->record($event->user, 'login', 'Logged in', $event->user, ['ip' => $ip]),
            $event instanceof Logout && $event->user instanceof Admin => $this->record($event->user, 'logout', 'Logged out', $event->user, ['ip' => $ip]),
            $event instanceof Failed => $this->record(null, 'login-failed', 'Failed login for '.($event->credentials['email'] ?? 'unknown'), $event->user instanceof Admin ? $event->user : null, ['ip' => $ip]),
            default => null,
        };
    }
}
