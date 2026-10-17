<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Profile\PasswordController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

/*
| User area — routes/web.php gets the "web" middleware group automatically.
*/

Route::middleware('area:web')->group(function () {
    Route::view('/', 'web.home')->name('home');

    // Typing /logout in the address bar shows a confirm page (logged out already → home)
    Route::get('logout', [LoginController::class, 'confirm'])->name('logout.confirm');

    // Only for visitors who are NOT logged in
    Route::middleware('guest:web')->group(function () {
        Route::get('register', [RegisterController::class, 'create'])->name('register');
        Route::post('register', [RegisterController::class, 'store']);

        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store']);

        Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [ForgotPasswordController::class, 'store'])
            ->middleware('throttle:6,1')->name('password.email');
        Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.store');
    });

    // Only for logged-in users
    Route::middleware('auth:web')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('profile/password', [PasswordController::class, 'update'])->name('profile.password');

        Route::get('email/verify', [VerifyEmailController::class, 'notice'])->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
        Route::post('email/verification-notification', [VerifyEmailController::class, 'send'])
            ->middleware('throttle:6,1')->name('verification.send');

        // Pages that need a verified email go in here, e.g.:
        // Route::middleware('verified')->group(function () { ... });
    });
});
