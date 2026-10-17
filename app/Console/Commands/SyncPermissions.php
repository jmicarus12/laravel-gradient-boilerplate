<?php

namespace App\Console\Commands;

use App\Enums\Permission as PermissionEnum;
use App\Models\Admin;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Makes the permissions table match App\Enums\Permission and ensures the
 * Super Admin role exists. Safe to run any time (deploys, seeders).
 */
#[Signature('permissions:sync')]
#[Description('Sync admin permissions from App\Enums\Permission into the database')]
class SyncPermissions extends Command
{
    public function handle(): int
    {
        $guard = PermissionEnum::GUARD;
        $names = array_map(fn (PermissionEnum $p) => $p->value, PermissionEnum::cases());

        foreach ($names as $name) {
            if (Permission::findOrCreate($name, $guard)->wasRecentlyCreated) {
                $this->components->info("Added {$name}");
            }
        }

        $stale = Permission::where('guard_name', $guard)->whereNotIn('name', $names)->get();
        foreach ($stale as $permission) {
            $permission->delete();
            $this->components->warn("Removed {$permission->name} (no longer in the enum)");
        }

        // guard_name matters: a role made for the "web" guard would never apply to admins.
        Role::findOrCreate(Admin::SUPER_ADMIN, $guard);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->components->info(count($names).' permissions in sync.');

        return self::SUCCESS;
    }
}
