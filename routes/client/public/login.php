<?php

use App\Http\Controllers\Client\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// No login required — but already-logged-in clients are sent to the dashboard.
Route::middleware('guest:client')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});

// Typing /client/logout in the address bar shows a confirm page (logged out already → login)
Route::get('logout', [LoginController::class, 'confirm'])->name('logout.confirm');
