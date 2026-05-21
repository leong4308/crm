<?php

use Aether\Admin\Http\Controllers\DashboardController;
use Aether\Admin\Http\Controllers\DataGrid\SavedFilterController;
use Aether\Admin\Http\Controllers\DataGridController;
use Aether\Admin\Http\Controllers\TinyMCEController;
use Aether\Admin\Http\Controllers\User\AccountController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del tablero.
 */
Route::controller(DashboardController::class)->prefix('dashboard')->group(function () {
    Route::get('', 'index')->name('admin.dashboard.index');

    Route::get('stats', 'stats')->name('admin.dashboard.stats');
});

/**
 * Rutas de DataGrid.
 */
Route::prefix('datagrid')->group(function () {
    /**
     * Rutas de filtro guardadas.
     */
    Route::controller(SavedFilterController::class)->prefix('datagrid/saved-filters')->group(function () {
        Route::post('', 'store')->name('admin.datagrid.saved_filters.store');

        Route::get('', 'get')->name('admin.datagrid.saved_filters.index');

        Route::put('{id}', 'update')->name('admin.datagrid.saved_filters.update');

        Route::delete('{id}', 'destroy')->name('admin.datagrid.saved_filters.destroy');
    });

    /**
     * Rutas de búsqueda.
     */
    Route::get('datagrid/look-up', [DataGridController::class, 'lookUp'])->name('admin.datagrid.look_up');
});

/**
 * Controlador de carga de archivos Tinymce.
 */
Route::post('tinymce/upload', [TinyMCEController::class, 'upload'])->name('admin.tinymce.upload');

/**
 * Rutas del perfil de usuario.
 */
Route::controller(AccountController::class)->prefix('account')->group(function () {
    Route::get('', 'edit')->name('admin.user.account.edit');

    Route::put('update', 'update')->name('admin.user.account.update');
});
