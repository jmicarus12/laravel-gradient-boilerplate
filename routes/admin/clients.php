<?php

use App\Http\Controllers\Admin\ClientController;
use Illuminate\Support\Facades\Route;

// Admins managing clients. Permissions (->can(...)) come later.

Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create')->can('create', Admin::class);
Route::post('clients', [ClientController::class, 'store'])->name('clients.store')->can('create', Admin::class);

Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit')->can('update', Admin::class);
Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update')->can('update', Admin::class);

Route::patch('clients/{client}/deactivate', [ClientController::class, 'deactivate'])->name('clients.deactivate')->can('deactivate', Admin::class);
Route::patch('clients/{client}/reactivate', [ClientController::class, 'reactivate'])->name('clients.reactivate')->can('deactivate', Admin::class);

Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy')->can('delete', Admin::class);
Route::patch('clients/{client}/restore', [ClientController::class, 'restore'])->name('clients.restore')->can('delete', Admin::class)->withTrashed();
