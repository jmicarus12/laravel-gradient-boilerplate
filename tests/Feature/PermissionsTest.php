<?php

namespace Tests\Feature;

use App\Enums\Permission as PermissionEnum;
use App\Models\Admin;
use App\Models\User;
use App\Theme\ThemeManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('permissions:sync')->assertSuccessful();
    }

    public function test_sync_creates_every_enum_permission_on_the_admin_guard(): void
    {
        foreach (PermissionEnum::cases() as $permission) {
            $this->assertDatabaseHas('permissions', ['name' => $permission->value, 'guard_name' => 'admin']);
        }

        $this->assertDatabaseHas('roles', ['name' => Admin::SUPER_ADMIN, 'guard_name' => 'admin']);
    }

    public function test_sync_removes_permissions_no_longer_in_the_enum_and_is_repeatable(): void
    {
        Permission::create(['name' => 'old.thing', 'guard_name' => 'admin']);

        $this->artisan('permissions:sync')->assertSuccessful();
        $this->artisan('permissions:sync')->assertSuccessful();

        $this->assertDatabaseMissing('permissions', ['name' => 'old.thing']);
        $this->assertSame(count(PermissionEnum::cases()), Permission::where('guard_name', 'admin')->count());
    }

    public function test_super_admins_pass_every_check(): void
    {
        $admin = Admin::factory()->create()->assignRole(Admin::SUPER_ADMIN);

        foreach (PermissionEnum::cases() as $permission) {
            $this->assertTrue($admin->can($permission));
        }

        $this->assertTrue($admin->can('anything.at.all'));
        $this->assertTrue($admin->can('delete', Admin::factory()->create()));
    }

    public function test_admins_only_get_what_their_role_grants(): void
    {
        $role = Role::create(['name' => 'Support', 'guard_name' => 'admin'])
            ->givePermissionTo(PermissionEnum::AdminsView);
        $admin = Admin::factory()->create()->assignRole($role);
        $other = Admin::factory()->create();

        $this->assertTrue($admin->can(PermissionEnum::AdminsView));
        $this->assertFalse($admin->can(PermissionEnum::AdminsCreate));

        $this->assertTrue($admin->can('viewAny', Admin::class));
        $this->assertFalse($admin->can('create', Admin::class));
        $this->assertFalse($admin->can('delete', $other));
    }

    public function test_admins_without_a_role_can_do_nothing(): void
    {
        $admin = Admin::factory()->create();

        foreach (PermissionEnum::cases() as $permission) {
            $this->assertFalse($admin->can($permission));
        }
    }

    public function test_only_super_admins_can_change_super_admin_accounts(): void
    {
        $manager = Admin::factory()->create()->givePermissionTo([
            PermissionEnum::AdminsUpdate, PermissionEnum::AdminsDelete, PermissionEnum::AdminsDeactivate,
        ]);
        $regular = Admin::factory()->create();
        $super = Admin::factory()->create()->assignRole(Admin::SUPER_ADMIN);

        $this->assertTrue($manager->can('update', $regular));
        $this->assertFalse($manager->can('update', $super));
        $this->assertFalse($manager->can('delete', $super));
        $this->assertFalse($manager->can('deactivate', $super));
    }

    public function test_role_permissions_are_per_guard(): void
    {
        // A role for the "web" guard can't be given admin permissions.
        $webRole = Role::create(['name' => 'Member', 'guard_name' => 'web']);

        $this->expectException(PermissionDoesNotExist::class);
        $webRole->givePermissionTo(PermissionEnum::AdminsView);
    }

    public function test_users_are_never_admins(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(Gate::forUser($user)->allows(PermissionEnum::AdminsView->value));
    }

    public function test_menu_hides_items_the_admin_may_not_see(): void
    {
        Route::get('/admin/admins', fn () => 'ok')->name('admin.admins.index');
        Route::get('/admin/roles', fn () => 'ok')->name('admin.roles.index');
        app('router')->getRoutes()->refreshNameLookups();

        $viewer = Admin::factory()->create()->givePermissionTo(PermissionEnum::AdminsView);
        $this->actingAs($viewer, 'admin');

        $access = collect(app(ThemeManager::class)->pinnedMenu('admin'))->firstWhere('label', 'Access');
        $labels = collect($access['children'])->pluck('label')->all();

        $this->assertContains('Admins', $labels);
        $this->assertNotContains('Roles', $labels);
    }

    public function test_pinned_section_disappears_without_any_access_permission(): void
    {
        Route::get('/admin/admins', fn () => 'ok')->name('admin.admins.index');
        Route::get('/admin/roles', fn () => 'ok')->name('admin.roles.index');
        app('router')->getRoutes()->refreshNameLookups();

        $this->actingAs(Admin::factory()->create(), 'admin');

        $this->assertSame([], app(ThemeManager::class)->pinnedMenu('admin')); // no "System" caption left behind
    }

    public function test_seeder_makes_the_dev_admin_a_super_admin(): void
    {
        $this->seed();

        $this->assertTrue(Admin::firstWhere('email', 'admin@example.com')->isSuperAdmin());
        $this->assertTrue(Role::findByName('Viewer', 'admin')->hasPermissionTo(PermissionEnum::RolesView));
    }
}
