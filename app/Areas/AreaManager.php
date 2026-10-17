<?php

namespace App\Areas;

use App\Theme\ThemeManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

/**
 * Switches the app into one area (config/areas.php): its session cookie,
 * session table, lifetime, default auth guard and theme.
 *
 * Must run BEFORE the session starts — see the UseArea middleware.
 */
class AreaManager
{
    protected ?string $current = null;

    /** config/session.php values as loaded, restored when an area has no override. */
    protected array $baseSession;

    public function __construct(protected Application $app, protected ThemeManager $theme)
    {
        $this->baseSession = $app['config']->get('session');
    }

    public function use(string $name): void
    {
        $area = $this->config($name);

        $this->useSession(array_merge($this->baseSession, $area['session'] ?? []));

        // auth()->user(), $request->user(), @auth, "auth" middleware without a guard
        // now all mean this area's guard. 'auth.driver' caches the old guard, so drop it.
        $this->app['auth']->shouldUse($area['guard']);
        $this->app->forgetInstance('auth.driver');

        $this->theme->use($name);

        $this->current = $name;
    }

    public function current(): string
    {
        return $this->current ?? $this->app['config']->get('areas.default');
    }

    /**
     * @return array<string, mixed>
     */
    public function config(?string $name = null): array
    {
        $name ??= $this->current();
        $area = $this->app['config']->get("areas.areas.{$name}");

        if (! is_array($area)) {
            throw new InvalidArgumentException("Area [{$name}] is not defined in config/areas.php.");
        }

        return $area;
    }

    public function guard(): string
    {
        return $this->config()['guard'];
    }

    public function loginUrl(): string
    {
        return $this->routeUrl($this->config()['login'] ?? null);
    }

    public function homeUrl(): string
    {
        return $this->routeUrl($this->config()['home'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $session
     */
    protected function useSession(array $session): void
    {
        $config = $this->app['config'];
        $keys = ['cookie', 'table', 'lifetime', 'expire_on_close'];

        $changed = false;
        foreach ($keys as $key) {
            if ($config->get("session.{$key}") !== $session[$key]) {
                $config->set("session.{$key}", $session[$key]);
                $changed = true;
            }
        }

        if ($changed) {
            // The session manager caches its store (with the old cookie name and table);
            // forget it so StartSession builds a new one from the updated config.
            $this->app['session']->forgetDrivers();
            $this->app->forgetInstance('session.store');

            // The redirector keeps a reference to the store it was created with; without this,
            // redirect()->with(...) flash messages would land in the old area's session.
            if ($this->app->resolved('redirect')) {
                $this->app['redirect']->setSession($this->app['session.store']);
            }
        }
    }

    protected function routeUrl(?string $name): string
    {
        return $name && Route::has($name) ? route($name) : url('/');
    }
}
