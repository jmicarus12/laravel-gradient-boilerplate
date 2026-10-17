<?php

use Illuminate\Support\Facades\Route;

// Preview of every theme component
Route::view('/components', 'admin.components')->name('components');

// Every Phosphor icon the theme loads — click one to copy its name for config/menu.php
Route::view('/icons', 'admin.icons')->name('icons');
