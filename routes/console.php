<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Remove Activity Log entries older than config('activitylog.clean_after_days').
// Needs the scheduler running on the server: * * * * * php artisan schedule:run
Schedule::command('activitylog:clean')->daily();
