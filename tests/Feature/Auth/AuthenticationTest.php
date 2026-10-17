<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Welcome back');
    }

    public function test_users_can_log_in(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_login_is_locked_after_too_many_attempts(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $i) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
        }

        // Even the right password is refused while locked out.
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertStringStartsWith('Too many login attempts', session('errors')->first('email'));

        $this->assertGuest('web');
    }

    public function test_users_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->post('/logout')->assertRedirect(route('home'));

        $this->assertGuest('web');
    }

    public function test_logged_in_users_cannot_see_login_or_register(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->get('/login')->assertRedirect();
        $this->actingAs($user, 'web')->get('/register')->assertRedirect();
    }

    public function test_after_login_users_return_to_the_page_they_wanted(): void
    {
        $user = User::factory()->create();

        $this->get('/profile')->assertRedirect(route('login'));

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('profile.edit'));
    }

    public function test_typing_logout_in_the_address_bar_asks_first(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->get('/logout')
            ->assertOk()
            ->assertSee('Log out?')
            ->assertSee(route('logout'), false);

        $this->assertAuthenticatedAs($user, 'web'); // a GET never logs out
    }

    public function test_logout_page_sends_guests_home(): void
    {
        $this->get('/logout')->assertRedirect(route('home'));
    }
}
