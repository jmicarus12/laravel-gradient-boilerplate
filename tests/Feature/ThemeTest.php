<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Theme\ThemeManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_pages_render_with_the_user_layout(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('pc-header-top', false)
            ->assertDontSee('pc-sidebar', false);
    }

    public function test_guest_pages_render_with_the_guest_layout(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('auth-main', false);
    }

    public function test_admin_pages_render_with_the_admin_layout_and_menu(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin');

        $this->get('/admin')
            ->assertOk()
            ->assertSee('pc-sidebar', false)
            ->assertSee('UI Components');

        $this->get('/admin/components')->assertOk();
        $this->get('/admin/icons')->assertOk()->assertSee('icon-grid', false);
    }

    public function test_admin_routes_switch_the_theme_area(): void
    {
        $this->get('/admin/login');

        $this->assertSame('admin', app(ThemeManager::class)->area());
    }

    public function test_menu_hides_items_whose_routes_do_not_exist_yet(): void
    {
        $labels = collect(app(ThemeManager::class)->menu('admin'))->pluck('label')->filter();

        $this->assertContains('Dashboard', $labels);
        $this->assertNotContains('Admins', $labels); // lives in the pinned menu (config/menu.php "pinned")
    }

    public function test_unknown_area_fails_loudly(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(ThemeManager::class)->use('nope');
    }

    public function test_missing_theme_folder_fails_loudly(): void
    {
        config(['areas.areas.admin.theme' => 'does-not-exist']);

        $this->expectException(InvalidArgumentException::class);

        app(ThemeManager::class)->use('admin');
    }
}
