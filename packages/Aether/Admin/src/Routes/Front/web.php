<?php

use Illuminate\Support\Facades\Route;
use Aether\Admin\Http\Controllers\Controller;

/**
 * Rutas de origen.
 */
Route::get('/', [Controller::class, 'redirectToLogin'])->name('aether.home');
