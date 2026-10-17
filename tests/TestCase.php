<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Refuse to run against anything but the test environment.
     *
     * With a cached config (php artisan config:cache / optimize) Laravel ignores the
     * test settings in phpunit.xml and uses .env, so RefreshDatabase would wipe the
     * real database. This runs before any test trait touches the database.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        if ($app->configurationIsCached() || ! $app->environment('testing')) {
            throw new RuntimeException(
                'Tests stopped to protect your database: the config is cached or APP_ENV is not "testing". '
                .'Run `php artisan optimize:clear` and try again.'
            );
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Tests don't need compiled CSS/JS (public/build).
        $this->withoutVite();
    }
}
