<?php

namespace Tests\Feature\Admin;

use App\Enums\Permission;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class AdminTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('permissions:sync');
    }

    protected function superAdmin(array $attributes = []): Admin
    {
        return Admin::factory()->create($attributes)->assignRole(Admin::SUPER_ADMIN);
    }

    /**
     * An admin whose only role grants exactly these permissions.
     *
     * @param  list<Permission>  $permissions
     */
    protected function adminWith(array $permissions, string $role = 'Tester'): Admin
    {
        $role = Role::findOrCreate($role, 'admin')->syncPermissions($permissions);

        return Admin::factory()->create()->assignRole($role);
    }
}
