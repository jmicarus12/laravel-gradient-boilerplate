<?php

namespace Tests\Feature\Admin;

use App\Enums\Permission;
use App\Models\Admin;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class ActivityLogTest extends AdminTestCase
{
    public function test_activity_page_requires_the_permission(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')->get('/admin/activity')->assertForbidden();
        $this->actingAs($this->adminWith([Permission::ActivityView]), 'admin')->get('/admin/activity')->assertOk();
    }

    public function test_admin_changes_are_recorded_with_who_and_what(): void
    {
        $super = $this->superAdmin(['name' => 'Boss']);
        Role::findOrCreate('Support', 'admin');
        $this->actingAs($super, 'admin');

        $this->post('/admin/admins', [
            'name' => 'Juan', 'email' => 'juan@example.com',
            'password' => 'secret-pass1', 'password_confirmation' => 'secret-pass1',
            'active' => '1', 'roles' => ['Support'],
        ]);
        $juan = Admin::firstWhere('email', 'juan@example.com');

        $this->put("/admin/admins/{$juan->id}", ['name' => 'Juan Cruz', 'email' => 'juan@example.com', 'active' => '1', 'roles' => []]);
        $this->patch("/admin/admins/{$juan->id}/deactivate");
        $this->delete("/admin/admins/{$juan->id}");

        $log = Activity::inLog('admin')->where('subject_id', $juan->id)->oldest('id')->get();

        $this->assertSame(['created', 'updated', 'deactivated', 'deleted'], $log->pluck('event')->all());
        $this->assertTrue($log->every(fn ($a) => $a->causer->is($super)));

        $update = $log[1];
        $this->assertSame(['from' => 'Juan', 'to' => 'Juan Cruz'], $update->properties['changes']['name']);
        $this->assertSame(['from' => ['Support'], 'to' => []], $update->properties['changes']['roles']);
    }

    public function test_passwords_are_never_written_to_the_log(): void
    {
        $this->actingAs($this->superAdmin(), 'admin')->post('/admin/admins', [
            'name' => 'Juan', 'email' => 'juan@example.com',
            'password' => 'secret-pass1', 'password_confirmation' => 'secret-pass1',
        ]);
        $juan = Admin::firstWhere('email', 'juan@example.com');
        $this->put("/admin/admins/{$juan->id}/password", ['password' => 'other-pass1', 'password_confirmation' => 'other-pass1']);

        $this->assertStringNotContainsString('pass1', Activity::all()->toJson());
        $this->assertDatabaseHas('activity_log', ['event' => 'password-changed']);
    }

    public function test_role_changes_are_recorded(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        $this->post('/admin/roles', ['name' => 'HR', 'permissions' => [Permission::AdminsView->value]]);
        $role = Role::findByName('HR', 'admin');
        $this->put("/admin/roles/{$role->id}", ['name' => 'People', 'permissions' => [Permission::RolesView->value]]);

        $updated = Activity::firstWhere('event', 'role-updated');
        $this->assertSame(['from' => 'HR', 'to' => 'People'], $updated->properties['renamed']);
        $this->assertSame([Permission::RolesView->value], $updated->properties['added']);
        $this->assertSame([Permission::AdminsView->value], $updated->properties['removed']);
    }

    public function test_admin_logins_logouts_and_failures_are_recorded(): void
    {
        $admin = Admin::factory()->create();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong-password']);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password']);
        $this->post('/admin/logout');

        $this->assertSame(['login-failed', 'login', 'logout'], Activity::inLog('admin')->oldest('id')->pluck('event')->all());
    }

    public function test_deactivated_admin_login_is_a_failed_login_not_a_login(): void
    {
        $admin = Admin::factory()->inactive()->create();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Your account has been deactivated.']);

        $this->assertSame(['login-failed'], Activity::pluck('event')->all());
    }

    public function test_user_logins_are_not_recorded(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertSame(0, Activity::count());
    }

    public function test_activity_page_shows_and_filters_entries(): void
    {
        $super = $this->superAdmin(['name' => 'Boss']);
        $this->actingAs($super, 'admin');
        $this->post('/admin/roles', ['name' => 'HR']);
        $this->post('/admin/roles', ['name' => 'Finance']);

        $this->get('/admin/activity')->assertSee('Created role HR')->assertSee('Created role Finance')->assertSee('Boss');
        $this->get('/admin/activity?search=Finance')->assertSee('Created role Finance')->assertDontSee('Created role HR');
        $this->get('/admin/activity?event=role-created')->assertSee('Created role HR');
        $this->get('/admin/activity?event=login')->assertDontSee('Created role HR');
    }
}
