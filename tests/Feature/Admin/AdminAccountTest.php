<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminAccountTest extends AdminTestCase
{
    public function test_any_admin_can_open_their_account(): void
    {
        $admin = Admin::factory()->create(); // no roles at all

        $this->actingAs($admin, 'admin')->get('/admin/account')->assertOk()->assertSee($admin->email);
    }

    public function test_admin_can_update_name_and_email(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->patch('/admin/account', ['name' => 'New Name', 'email' => 'mine@example.com'])
            ->assertSessionHasNoErrors();

        $this->assertSame('mine@example.com', $admin->fresh()->email);
    }

    public function test_password_change_checks_the_admin_password_not_a_user_password(): void
    {
        $admin = Admin::factory()->create(['email' => 'same@example.com', 'password' => 'admin-pass1']);
        User::factory()->create(['email' => 'same@example.com', 'password' => 'user-pass1']);

        $this->actingAs($admin, 'admin');

        $this->put('/admin/account/password', ['current_password' => 'user-pass1', 'password' => 'next-pass1', 'password_confirmation' => 'next-pass1'])
            ->assertSessionHasErrors('current_password');

        $this->put('/admin/account/password', ['current_password' => 'admin-pass1', 'password' => 'next-pass1', 'password_confirmation' => 'next-pass1'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('next-pass1', $admin->fresh()->password));
    }
}
