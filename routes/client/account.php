<?php

use App\Http\Controllers\Client\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// The logged-in client's own session.
Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
