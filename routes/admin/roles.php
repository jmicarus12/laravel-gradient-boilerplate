<?php

use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

// Roles. ->can(...) runs App\Policies\RolePolicy (registered in AppServiceProvider).

Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->can('viewAny', Role::class);
Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->can('create', Role::class);
Route::post('roles', [RoleController::class, 'store'])->name('roles.store')->can('create', Role::class);
Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->can('update', 'role');
Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update')->can('update', 'role');
Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->can('delete', 'role');
