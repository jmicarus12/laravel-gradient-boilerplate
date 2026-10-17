<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Each area (config/areas.php) has its own session cookie and session table,
 * so logging in or out of one area never touches the other.
 */
class AreaSessionTest extends TestCase
{
    use RefreshDatabase;

    protected string $webCookie;

    protected string $adminCookie;

    protected function setUp(): void
    {
        parent::setUp();

        // Use database sessions like the real app (phpunit.xml defaults to "array").
        config(['session.driver' => 'database']);
        $this->app['session']->forgetDrivers();
        $this->app->forgetInstance('session.store');

        $this->webCookie = config('session.cookie');
        $this->adminCookie = config('areas.areas.admin.session.cookie');
    }

    public function test_each_area_uses_its_own_cookie(): void
    {
        $this->get('/')
            ->assertCookie($this->webCookie)
            ->assertCookieMissing($this->adminCookie);

        $this->get('/admin')
            ->assertCookie($this->adminCookie)
            ->assertCookieMissing($this->webCookie);

        $this->assertNotSame($this->webCookie, $this->adminCookie);
    }

    public function test_each_area_stores_sessions_in_its_own_table(): void
    {
        $this->get('/');
        $this->assertSame(1, DB::table('sessions')->count());
        $this->assertSame(0, DB::table('admin_sessions')->count());

        $this->get('/admin');
        $this->assertSame(1, DB::table('sessions')->count());
        $this->assertSame(1, DB::table('admin_sessions')->count());
    }

    public function test_user_and_admin_can_be_logged_in_at_the_same_time(): void
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $webSession = $this->sessionId($this->post('/login', ['email' => $user->email, 'password' => 'password']), $this->webCookie);
        $adminSession = $this->sessionId($this->post('/admin/login', ['email' => $admin->email, 'password' => 'password']), $this->adminCookie);

        $this->assertNotSame($webSession, $adminSession);
        $this->assertSame($user->id, DB::table('sessions')->where('id', $webSession)->value('user_id'));
        $this->assertSame($admin->id, DB::table('admin_sessions')->where('id', $adminSession)->value('user_id'));
    }

    public function test_logging_out_of_the_admin_area_keeps_the_user_logged_in(): void
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $webSession = $this->sessionId($this->post('/login', ['email' => $user->email, 'password' => 'password']), $this->webCookie);
        $adminSession = $this->sessionId($this->post('/admin/login', ['email' => $admin->email, 'password' => 'password']), $this->adminCookie);

        $this->withCookies([$this->webCookie => $webSession, $this->adminCookie => $adminSession])
            ->post('/admin/logout')
            ->assertRedirect(route('admin.login'));

        $this->assertDatabaseMissing('admin_sessions', ['id' => $adminSession]);                  // admin session gone
        $this->assertDatabaseHas('sessions', ['id' => $webSession, 'user_id' => $user->id]);       // user untouched
    }

    public function test_logging_out_of_the_user_area_keeps_the_admin_logged_in(): void
    {
        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $webSession = $this->sessionId($this->post('/login', ['email' => $user->email, 'password' => 'password']), $this->webCookie);
        $adminSession = $this->sessionId($this->post('/admin/login', ['email' => $admin->email, 'password' => 'password']), $this->adminCookie);

        // Browser sends both cookies, as a real one would.
        $this->withCookies([$this->webCookie => $webSession, $this->adminCookie => $adminSession])
            ->post('/logout')
            ->assertRedirect(route('home'));

        $this->assertDatabaseMissing('sessions', ['id' => $webSession]);       // user session gone
        $this->assertDatabaseHas('admin_sessions', ['id' => $adminSession, 'user_id' => $admin->id]); // admin untouched
    }

    public function test_the_admin_area_does_not_see_the_logged_in_user(): void
    {
        $user = User::factory()->create();

        $webSession = $this->sessionId($this->post('/login', ['email' => $user->email, 'password' => 'password']), $this->webCookie);

        // A logged-in user is still a guest in the admin area.
        $this->withCookies([$this->webCookie => $webSession])->get('/admin')->assertRedirect(route('admin.login'));

        $this->assertSame('admin', Auth::getDefaultDriver());
        $this->assertNull(auth()->user()); // auth() now means the admin guard — no admin logged in
    }

    public function test_admin_session_has_its_own_lifetime(): void
    {
        $this->get('/admin');
        $this->assertSame(30, config('session.lifetime'));

        $this->get('/');
        $this->assertSame((int) env('SESSION_LIFETIME', 120), config('session.lifetime'));
    }

    protected function sessionId(TestResponse $response, string $cookie): string
    {
        return $response->getCookie($cookie)->getValue();
    }
}
