<?php

namespace App\Theme;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

/**
 * Points the "theme::" view namespace at the active theme's folder.
 *
 * Pages use <x-ui::card>, <x-ui::layouts.admin>, ... which resolve to
 * resources/themes/{theme}/components/... at render time, so the same
 * page renders with whichever theme its area is configured to use.
 */
class ThemeManager
{
    protected string $area;

    protected string $name;

    public function use(string $area): void
    {
        $name = config("areas.areas.{$area}.theme");

        if (! $name) {
            throw new InvalidArgumentException("No theme configured for area [{$area}] in config/areas.php.");
        }

        $path = resource_path("themes/{$name}");

        if (! is_dir($path)) {
            throw new InvalidArgumentException("Theme [{$name}] not found at [{$path}].");
        }

        View::replaceNamespace('theme', $path);
        View::getFinder()->flush();

        $this->area = $area;
        $this->name = $name;
    }

    public function area(): string
    {
        return $this->area;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * URL of one of the area's named routes from config/areas.php — "home", "login",
     * "register", "account", "logout" — or null when the area has none (yet).
     * Lets one layout serve every area: $theme->url('logout').
     */
    public function url(string $key): ?string
    {
        $name = config("areas.areas.{$this->area}.{$key}");

        return $name && Route::has($name) ? route($name) : null;
    }

    /**
     * Vite entry points of the active theme, for @vite(...).
     *
     * @return list<string>
     */
    public function vite(): array
    {
        return [
            "resources/themes/{$this->name}/scss/app.scss",
            "resources/themes/{$this->name}/js/app.js",
        ];
    }

    /**
     * Menu for the active area (or another one), without items whose route doesn't
     * exist yet or whose "can" permission the current account lacks; active item flagged.
     *
     * @return list<array<string, mixed>>
     */
    public function menu(?string $area = null): array
    {
        return $this->prepareMenu(config('menu.'.($area ?? $this->area), []));
    }

    /**
     * Menu pinned to the bottom of the sidebar (config/menu.php "pinned").
     *
     * @return list<array<string, mixed>>
     */
    public function pinnedMenu(?string $area = null): array
    {
        return $this->prepareMenu(config('menu.pinned.'.($area ?? $this->area), []));
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    protected function prepareMenu(array $items): array
    {
        $prepared = [];

        foreach ($items as $item) {
            if (isset($item['caption'])) {
                $prepared[] = $item;

                continue;
            }

            if (isset($item['can']) && Gate::denies($item['can'])) {
                continue;
            }

            if (isset($item['children'])) {
                $item['children'] = $this->prepareMenu($item['children']);

                if ($item['children'] === []) {
                    continue;
                }

                $item['url'] = '#';
                $item['is_active'] = collect($item['children'])->contains('is_active', true);
            } elseif (isset($item['route']) && Route::has($item['route'])) {
                $item['url'] = route($item['route']);
                $item['is_active'] = request()->routeIs($item['active'] ?? $item['route']);
            } else {
                continue;
            }

            $prepared[] = $item;
        }

        // Drop captions that ended up with no items under them.
        return array_values(array_filter($prepared, function ($item, $i) use ($prepared) {
            return ! isset($item['caption']) || (isset($prepared[$i + 1]) && ! isset($prepared[$i + 1]['caption']));
        }, ARRAY_FILTER_USE_BOTH));
    }
}
