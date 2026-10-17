<?php

namespace Tests\Feature\Admin;

use App\Enums\Permission;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminManagementTest extends AdminTestCase
{
    public function test_admins_list_requires_the_view_permission(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')->get('/admin/admins')->assertForbidden();
        $this->actingAs($this->adminWith([Permission::AdminsView]), 'admin')->get('/admin/admins')->assertOk();
    }

    public function test_list_can_be_searched_and_filtered(): void
    {
        $super = $this->superAdmin(['name' => 'Super Person']);
        Admin::factory()->create(['name' => 'Maria Santos']);
        Admin::factory()->inactive()->create(['name' => 'Pedro Inactive']);
        Admin::factory()->create(['name' => 'Gone Admin'])->delete();

        $this->actingAs($super, 'admin');

        $this->get('/admin/admins?search=maria')->assertSee('Maria Santos')->assertDontSee('Pedro Inactive');
        $this->get('/admin/admins?status=deactivated')->assertSee('Pedro Inactive')->assertDontSee('Maria Santos');
        $this->get('/admin/admins?status=deleted')->assertSee('Gone Admin')->assertDontSee('Maria Santos');
        $this->get('/admin/admins')->assertDontSee('Gone Admin');
    }

    public function test_super_admin_can_create_an_admin_with_roles(): void
    {
        Role::findOrCreate('Support', 'admin');

        $this->actingAs($this->superAdmin(), 'admin')
            ->post('/admin/admins', [
                'name' => 'New Person',
                'email' => 'new@example.com',
                'password' => 'secret-pass1',
                'password_confirmation' => 'secret-pass1',
                'active' => '1',
                'roles' => ['Support'],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.admins.index'));

        $admin = Admin::firstWhere('email', 'new@example.com');
        $this->assertTrue($admin->hasRole('Support'));
        $this->assertTrue(Hash::check('secret-pass1', $admin->password));
    }

    public function test_admins_cannot_hand_out_roles_with_more_power_than_they_have(): void
    {
        Role::findOrCreate('Powerful', 'admin')->syncPermissions([Permission::AdminsDelete]);
        $creator = $this->adminWith([Permission::AdminsCreate, Permission::AdminsView]);

        $this->actingAs($creator, 'admin')
            ->post('/admin/admins', [
                'name' => 'Sneaky', 'email' => 'sneaky@example.com',
                'password' => 'secret-pass1', 'password_confirmation' => 'secret-pass1',
                'roles' => ['Powerful'],
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('admins', ['email' => 'sneaky@example.com']);
    }

    public function test_only_super_admins_can_assign_the_super_admin_role(): void
    {
        $creator = $this->adminWith(Permission::cases()); // every permission, but not Super Admin

        $this->actingAs($creator, 'admin')
            ->post('/admin/admins', [
                'name' => 'Sneaky', 'email' => 'sneaky@example.com',
                'password' => 'secret-pass1', 'password_confirmation' => 'secret-pass1',
                'roles' => [Admin::SUPER_ADMIN],
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('admins', ['email' => 'sneaky@example.com']);
    }

    public function test_admins_cannot_change_their_own_roles(): void
    {
        $admin = $this->adminWith([Permission::AdminsUpdate]);
        Role::findOrCreate('Extra', 'admin');

        $this->actingAs($admin, 'admin')
            ->put("/admin/admins/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'active' => '1', 'roles' => ['Tester', 'Extra']])
            ->assertSessionHas('error', "You can't change your own roles.");

        $this->assertFalse($admin->fresh()->hasRole('Extra'));
    }

    public function test_admin_details_and_roles_can_be_updated(): void
    {
        Role::findOrCreate('Support', 'admin');
        $target = Admin::factory()->create();

        $this->actingAs($this->superAdmin(), 'admin')
            ->put("/admin/admins/{$target->id}", ['name' => 'Renamed', 'email' => 'renamed@example.com', 'active' => '1', 'roles' => ['Support']])
            ->assertSessionHasNoErrors();

        $target->refresh();
        $this->assertSame('Renamed', $target->name);
        $this->assertTrue($target->hasRole('Support'));
    }

    public function test_non_super_admins_cannot_edit_super_admins(): void
    {
        $super = $this->superAdmin();
        $editor = $this->adminWith([Permission::AdminsView, Permission::AdminsUpdate, Permission::AdminsDelete]);

        $this->actingAs($editor, 'admin');
        $this->get("/admin/admins/{$super->id}/edit")->assertForbidden();
        $this->delete("/admin/admins/{$super->id}")->assertForbidden();
    }

    public function test_password_can_be_set_by_an_admin_manager(): void
    {
        $target = Admin::factory()->create();

        $this->actingAs($this->superAdmin(), 'admin')
            ->put("/admin/admins/{$target->id}/password", ['password' => 'fresh-pass1', 'password_confirmation' => 'fresh-pass1'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('fresh-pass1', $target->fresh()->password));
    }

    public function test_deactivating_logs_the_admin_out_everywhere(): void
    {
        $target = Admin::factory()->create();
        DB::table('admin_sessions')->insert(['id' => 'abc', 'user_id' => $target->id, 'payload' => '', 'last_activity' => time()]);

        $this->actingAs($this->superAdmin(), 'admin')->patch("/admin/admins/{$target->id}/deactivate")->assertSessionHas('success');

        $this->assertFalse($target->fresh()->active);
        $this->assertDatabaseMissing('admin_sessions', ['id' => 'abc']);

        $this->patch("/admin/admins/{$target->id}/reactivate");
        $this->assertTrue($target->fresh()->active);
    }

    public function test_admins_cannot_deactivate_delete_or_clear_themselves(): void
    {
        $super = $this->superAdmin();
        $this->actingAs($super, 'admin');

        $this->patch("/admin/admins/{$super->id}/deactivate")->assertSessionHas('error');
        $this->delete("/admin/admins/{$super->id}")->assertSessionHas('error');
        $this->delete("/admin/admins/{$super->id}/sessions")->assertSessionHas('error');

        $super->refresh();
        $this->assertTrue($super->active);
        $this->assertFalse($super->trashed());
    }

    public function test_clear_sessions_only_touches_admin_sessions_of_that_admin(): void
    {
        $target = Admin::factory()->create();
        $other = Admin::factory()->create();
        DB::table('admin_sessions')->insert([
            ['id' => 't1', 'user_id' => $target->id, 'payload' => '', 'last_activity' => time()],
            ['id' => 't2', 'user_id' => $target->id, 'payload' => '', 'last_activity' => time()],
            ['id' => 'o1', 'user_id' => $other->id, 'payload' => '', 'last_activity' => time()],
        ]);
        DB::table('sessions')->insert(['id' => 'u1', 'user_id' => $target->id, 'payload' => '', 'last_activity' => time()]);

        $this->actingAs($this->superAdmin(), 'admin')
            ->delete("/admin/admins/{$target->id}/sessions")
            ->assertSessionHas('success', "{$target->name} was logged out of 2 sessions.");

        $this->assertDatabaseMissing('admin_sessions', ['id' => 't1']);
        $this->assertDatabaseHas('admin_sessions', ['id' => 'o1']);
        $this->assertDatabaseHas('sessions', ['id' => 'u1']); // a *user* with the same id is untouched
    }

    public function test_deleted_admins_can_be_restored(): void
    {
        $target = Admin::factory()->create();
        $this->actingAs($this->superAdmin(), 'admin');

        $this->delete("/admin/admins/{$target->id}")->assertRedirect(route('admin.admins.index'));
        $this->assertSoftDeleted($target);

        $this->patch("/admin/admins/{$target->id}/restore")->assertRedirect(route('admin.admins.index'));
        $this->assertNotSoftDeleted($target);
    }

    public function test_pages_render(): void
    {
        $target = Admin::factory()->create();
        $this->actingAs($this->superAdmin(), 'admin');

        $this->get('/admin/admins/create')->assertOk()->assertSee('Create admin');
        $this->get("/admin/admins/{$target->id}/edit")->assertOk()->assertSee($target->email);
        $this->get('/admin')->assertOk()->assertSee('Recent admin logins');
    }
}
