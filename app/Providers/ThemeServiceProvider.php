<?php

namespace App\Providers;

use App\Theme\ThemeManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThemeManager::class);
    }

    public function boot(ThemeManager $theme): void
    {
        // <x-ui::card> → theme::components.card → resources/themes/{active}/components/card.blade.php
        Blade::anonymousComponentNamespace('theme::components', 'ui');

        View::share('theme', $theme);
    }
}
