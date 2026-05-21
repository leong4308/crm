<?php

use Aether\Admin\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

/**
 * Rutas de origen.
 */
Route::get('/', [Controller::class, 'redirectToLogin'])->name('aether.home');
