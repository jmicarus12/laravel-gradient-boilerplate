<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/components')->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Admin panel');
    }

    public function test_admins_can_log_in(): void
    {
        $admin = Admin::factory()->create();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->get('/admin')->assertOk()->assertSee($admin->name);
    }

    public function test_login_records_time_and_ip(): void
    {
        $admin = Admin::factory()->create();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password']);

        $admin->refresh();
        $this->assertNotNull($admin->last_login_at);
        $this->assertSame('127.0.0.1', $admin->last_login_ip);
    }

    public function test_user_accounts_cannot_log_into_admin(): void
    {
        $user = User::factory()->create();

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $admin = Admin::factory()->create();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_deactivated_admins_cannot_log_in(): void
    {
        $admin = Admin::factory()->inactive()->create();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Your account has been deactivated.']);

        $this->assertGuest('admin');
    }

    public function test_admins_deactivated_while_logged_in_are_logged_out(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->get('/admin')->assertOk();

        $admin->update(['active' => false]);

        $this->get('/admin')
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('error', 'Your account has been deactivated.');

        $this->assertGuest('admin');
    }

    public function test_logged_in_admins_skip_the_login_page(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->get('/admin/login')->assertRedirect(route('admin.dashboard'));
    }

    public function test_admins_can_log_out(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->post('/admin/logout')->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    }

    public function test_after_login_admins_return_to_the_page_they_wanted(): void
    {
        $admin = Admin::factory()->create();

        $this->get('/admin/components')->assertRedirect(route('admin.login'));

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.components'));
    }

    public function test_typing_admin_logout_in_the_address_bar_asks_first(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->get('/admin/logout')
            ->assertOk()
            ->assertSee('Log out?')
            ->assertSee(route('admin.logout'), false);

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_logout_page_sends_guests_to_login(): void
    {
        $this->get('/admin/logout')->assertRedirect(route('admin.login'));
    }
}
