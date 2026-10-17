<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// The logged-in admin's own account — every admin may use these.
Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
Route::patch('account', [AccountController::class, 'update'])->name('account.update');
Route::put('account/password', [AccountController::class, 'updatePassword'])->name('account.password');
