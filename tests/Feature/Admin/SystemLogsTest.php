<?php

namespace Tests\Feature\Admin;

use App\Enums\Permission;
use App\Models\User;

class SystemLogsTest extends AdminTestCase
{
    public function test_guests_are_sent_to_the_admin_login(): void
    {
        $this->get('/admin/system-logs')->assertRedirect(route('admin.login'));
    }

    public function test_only_super_admins_can_open_system_logs(): void
    {
        $this->actingAs($this->adminWith(Permission::cases()), 'admin')->get('/admin/system-logs')->assertForbidden();
        $this->actingAs($this->superAdmin(), 'admin')->get('/admin/system-logs')->assertOk();
    }

    public function test_the_api_behind_it_is_super_admin_only_too(): void
    {
        $this->actingAs($this->adminWith(Permission::cases()), 'admin')->getJson('/admin/system-logs/api/files')->assertForbidden();
        $this->actingAs($this->superAdmin(), 'admin')->getJson('/admin/system-logs/api/files')->assertOk();
    }

    public function test_menu_shows_system_logs_only_to_super_admins(): void
    {
        $everythingButSuper = $this->adminWith(Permission::cases());

        $this->actingAs($everythingButSuper, 'admin')->get('/admin')->assertSee('Activity Log')->assertDontSee('System Logs');
        $this->actingAs($this->superAdmin(), 'admin')->get('/admin')->assertSee('System Logs');
    }

    public function test_logged_in_users_are_not_admins_here(): void
    {
        $this->actingAs(User::factory()->create(), 'web')->get('/admin/system-logs')->assertRedirect(route('admin.login'));
    }
}
