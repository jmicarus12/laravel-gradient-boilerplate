<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('permissions:sync');

        // Example role, editable in the admin UI (step 9). Roles are just bundles of permissions.
        Role::findOrCreate('Viewer', Permission::GUARD)->syncPermissions([
            Permission::AdminsView,
            Permission::RolesView,
            Permission::ActivityView,
        ]);

        Admin::firstWhere('email', 'admin@example.com')?->assignRole(Admin::SUPER_ADMIN);
    }
}
