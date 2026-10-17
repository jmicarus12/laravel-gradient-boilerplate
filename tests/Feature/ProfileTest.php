<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/profile')->assertRedirect(route('login'));
    }

    public function test_profile_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertOk()->assertSee($user->email);
    }

    public function test_profile_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', ['name' => 'New Name', 'email' => 'new@example.com'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_keeping_the_same_email_keeps_it_verified(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', ['name' => 'New Name', 'email' => $user->email]);

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_password_can_be_changed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'password',
                'password' => 'new-password1',
                'password_confirmation' => 'new-password1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password1', $user->refresh()->password));
    }

    public function test_current_password_must_be_correct_to_change_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password1',
                'password_confirmation' => 'new-password1',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_account_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/profile', ['delete_password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertGuest('web');
        $this->assertModelMissing($user);
    }

    public function test_correct_password_is_required_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/profile', ['delete_password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('deleteAccount', 'delete_password');

        $this->assertModelExists($user);
    }
}
