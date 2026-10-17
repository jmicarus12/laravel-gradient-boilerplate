<?php

use App\Http\Controllers\Admin\AdminController;
use App\Models\Admin;
use Illuminate\Support\Facades\Route;

/*
| Admin accounts. ->can('ability', ...) runs App\Policies\AdminPolicy:
|   ->can('viewAny', Admin::class)  no record involved
|   ->can('update', 'admin')        the {admin} from the URL is passed to the policy
*/

Route::get('admins', [AdminController::class, 'index'])->name('admins.index')->can('viewAny', Admin::class);
Route::get('admins/create', [AdminController::class, 'create'])->name('admins.create')->can('create', Admin::class);
Route::post('admins', [AdminController::class, 'store'])->name('admins.store')->can('create', Admin::class);

Route::get('admins/{admin}/edit', [AdminController::class, 'edit'])->name('admins.edit')->can('update', 'admin');
Route::put('admins/{admin}', [AdminController::class, 'update'])->name('admins.update')->can('update', 'admin');
Route::put('admins/{admin}/password', [AdminController::class, 'updatePassword'])->name('admins.password')->can('update', 'admin');

Route::patch('admins/{admin}/deactivate', [AdminController::class, 'deactivate'])->name('admins.deactivate')->can('deactivate', 'admin');
Route::patch('admins/{admin}/reactivate', [AdminController::class, 'reactivate'])->name('admins.reactivate')->can('deactivate', 'admin');
Route::delete('admins/{admin}/sessions', [AdminController::class, 'clearSessions'])->name('admins.sessions.destroy')->can('clearSessions', 'admin');

Route::delete('admins/{admin}', [AdminController::class, 'destroy'])->name('admins.destroy')->can('delete', 'admin');
Route::patch('admins/{admin}/restore', [AdminController::class, 'restore'])->name('admins.restore')->can('restore', 'admin')->withTrashed();
