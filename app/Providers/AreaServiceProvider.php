<?php

namespace App\Providers;

use App\Areas\AreaManager;
use Illuminate\Support\ServiceProvider;

class AreaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AreaManager::class);
    }

    public function boot(AreaManager $areas): void
    {
        // Until a route picks one (console, mail, error pages), use the default area.
        $areas->use(config('areas.default'));
    }
}
