<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// No login required — but already-logged-in admins are sent to the dashboard.
Route::middleware('guest:admin')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});

// Typing /admin/logout in the address bar shows a confirm page (logged out already → login)
Route::get('logout', [LoginController::class, 'confirm'])->name('logout.confirm');
