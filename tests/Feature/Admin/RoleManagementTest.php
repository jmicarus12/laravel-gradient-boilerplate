<?php

namespace Tests\Feature\Admin;

use App\Enums\Permission;
use App\Models\Admin;
use Spatie\Permission\Models\Role;

class RoleManagementTest extends AdminTestCase
{
    public function test_roles_list_requires_the_view_permission(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')->get('/admin/roles')->assertForbidden();
        $this->actingAs($this->adminWith([Permission::RolesView]), 'admin')->get('/admin/roles')->assertOk()->assertSee(Admin::SUPER_ADMIN);
    }

    public function test_a_role_can_be_created_with_permissions(): void
    {
        $this->actingAs($this->superAdmin(), 'admin')
            ->post('/admin/roles', ['name' => 'HR', 'permissions' => [Permission::AdminsView->value, Permission::RolesView->value]])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.roles.index'));

        $role = Role::findByName('HR', 'admin');
        $this->assertTrue($role->hasPermissionTo(Permission::AdminsView));
        $this->assertFalse($role->hasPermissionTo(Permission::AdminsDelete));
    }

    public function test_role_names_are_unique_and_super_admin_is_reserved(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        $this->post('/admin/roles', ['name' => 'HR'])->assertSessionHasNoErrors();
        $this->post('/admin/roles', ['name' => 'HR'])->assertSessionHasErrors('name');
        $this->post('/admin/roles', ['name' => 'super admin'])->assertSessionHasErrors(['name' => 'That role name is reserved.']);
    }

    public function test_unknown_permissions_are_rejected(): void
    {
        $this->actingAs($this->superAdmin(), 'admin')
            ->post('/admin/roles', ['name' => 'Odd', 'permissions' => ['made.up']])
            ->assertSessionHasErrors('permissions.0');
    }

    public function test_a_role_can_be_renamed_and_its_permissions_changed(): void
    {
        $role = Role::findOrCreate('Support', 'admin')->syncPermissions([Permission::AdminsView]);

        $this->actingAs($this->superAdmin(), 'admin')
            ->put("/admin/roles/{$role->id}", ['name' => 'Helpdesk', 'permissions' => [Permission::RolesView->value]])
            ->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertSame('Helpdesk', $role->name);
        $this->assertTrue($role->hasPermissionTo(Permission::RolesView));
        $this->assertFalse($role->hasPermissionTo(Permission::AdminsView));
    }

    public function test_the_super_admin_role_is_locked(): void
    {
        $role = Role::findByName(Admin::SUPER_ADMIN, 'admin');
        $this->actingAs($this->superAdmin(), 'admin');

        $this->get("/admin/roles/{$role->id}/edit")->assertRedirect(route('admin.roles.index'));
        $this->put("/admin/roles/{$role->id}", ['name' => 'Renamed'])->assertSessionHas('error');
        $this->delete("/admin/roles/{$role->id}")->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => Admin::SUPER_ADMIN]);
    }

    public function test_roles_cannot_grant_permissions_the_editor_lacks(): void
    {
        $editor = $this->adminWith([Permission::RolesView, Permission::RolesManage], 'Role Manager');

        $this->actingAs($editor, 'admin')
            ->post('/admin/roles', ['name' => 'Escalate', 'permissions' => [Permission::AdminsDelete->value]])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('roles', ['name' => 'Escalate']);
    }

    public function test_admins_cannot_edit_a_role_they_have(): void
    {
        $editor = $this->adminWith([Permission::RolesView, Permission::RolesManage], 'Role Manager');
        $role = Role::findByName('Role Manager', 'admin');

        $this->actingAs($editor, 'admin')
            ->put("/admin/roles/{$role->id}", ['name' => 'Role Manager', 'permissions' => [Permission::RolesView->value]])
            ->assertSessionHas('error');

        $this->assertTrue($role->fresh()->hasPermissionTo(Permission::RolesManage));
    }

    public function test_roles_in_use_cannot_be_deleted(): void
    {
        $role = Role::findOrCreate('Support', 'admin');
        Admin::factory()->create()->assignRole($role);

        $this->actingAs($this->superAdmin(), 'admin');

        $this->delete("/admin/roles/{$role->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['name' => 'Support']);

        $role->users()->detach();
        $this->delete("/admin/roles/{$role->id}")->assertSessionHas('success');
        $this->assertDatabaseMissing('roles', ['name' => 'Support']);
    }
}
