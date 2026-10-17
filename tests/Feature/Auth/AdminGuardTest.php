<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * The "admin" guard reads the admins table; the "web" guard reads users.
 * They never accept each other's accounts.
 */
class AdminGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_guard_logs_in_admins(): void
    {
        $admin = Admin::factory()->create(['email' => 'boss@example.com']);

        $this->assertTrue(Auth::guard('admin')->attempt(['email' => 'boss@example.com', 'password' => 'password']));
        $this->assertTrue(Auth::guard('admin')->user()->is($admin));
        $this->assertInstanceOf(Admin::class, Auth::guard('admin')->user());
    }

    public function test_web_guard_does_not_know_admins(): void
    {
        Admin::factory()->create(['email' => 'boss@example.com']);

        $this->assertFalse(Auth::guard('web')->attempt(['email' => 'boss@example.com', 'password' => 'password']));
    }

    public function test_admin_guard_does_not_know_users(): void
    {
        User::factory()->create(['email' => 'juan@example.com']);

        $this->assertFalse(Auth::guard('admin')->attempt(['email' => 'juan@example.com', 'password' => 'password']));
    }

    public function test_same_email_can_exist_in_both_tables_as_different_accounts(): void
    {
        $user = User::factory()->create(['email' => 'same@example.com', 'password' => 'user-pass1']);
        $admin = Admin::factory()->create(['email' => 'same@example.com', 'password' => 'admin-pass1']);

        $this->assertTrue(Auth::guard('web')->attempt(['email' => 'same@example.com', 'password' => 'user-pass1']));
        $this->assertTrue(Auth::guard('admin')->attempt(['email' => 'same@example.com', 'password' => 'admin-pass1']));

        $this->assertTrue(Auth::guard('web')->user()->is($user));
        $this->assertTrue(Auth::guard('admin')->user()->is($admin));

        // Each password only works for its own account.
        Auth::guard('admin')->logout();
        $this->assertFalse(Auth::guard('admin')->attempt(['email' => 'same@example.com', 'password' => 'user-pass1']));
    }

    public function test_soft_deleted_admins_cannot_log_in(): void
    {
        Admin::factory()->create(['email' => 'gone@example.com'])->delete();

        $this->assertFalse(Auth::guard('admin')->attempt(['email' => 'gone@example.com', 'password' => 'password']));
    }

    public function test_seeder_creates_the_dev_admin(): void
    {
        $this->seed();

        $this->assertDatabaseHas('admins', ['email' => 'admin@example.com', 'active' => true]);
        $this->assertTrue(User::firstWhere('email', 'test@example.com')->hasVerifiedEmail());
    }
}
