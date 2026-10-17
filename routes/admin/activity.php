<?php

use App\Enums\Permission;
use App\Http\Controllers\Admin\ActivityController;
use Illuminate\Support\Facades\Route;

Route::get('activity', [ActivityController::class, 'index'])->name('activity.index')->can(Permission::ActivityView);

// System Logs (Laravel's log files) live at /admin/system-logs — see config/log-viewer.php.
