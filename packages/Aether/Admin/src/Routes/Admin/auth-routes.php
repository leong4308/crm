<?php

use Aether\Admin\Http\Controllers\Controller;
use Aether\Admin\Http\Controllers\User\ForgotPasswordController;
use Aether\Admin\Http\Controllers\User\ResetPasswordController;
use Aether\Admin\Http\Controllers\User\SessionController;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware(['user'])->group(function () {
    /**
     * Ruta de redireccionamiento.
     */
    Route::get('/', [Controller::class, 'redirectToLogin']);

    /**
     * Rutas de sesión.
     */
    Route::controller(SessionController::class)->group(function () {
        Route::prefix('login')->group(function () {
            Route::get('', 'create')->name('admin.session.create');

            Route::post('', 'store')->name('admin.session.store');
        });

        Route::middleware(['user'])->group(function () {
            Route::delete('logout', 'destroy')->name('admin.session.destroy');
        });
    });

    /**
     * Olvidé las rutas de contraseña.
     */
    Route::controller(ForgotPasswordController::class)->prefix('forget-password')->group(function () {
        Route::get('', 'create')->name('admin.forgot_password.create');

        Route::post('', 'store')->name('admin.forgot_password.store');
    });

    /**
     * Restablecer rutas de contraseña.
     */
    Route::controller(ResetPasswordController::class)->prefix('reset-password')->group(function () {
        Route::get('{token}', 'create')->name('admin.reset_password.create');

        Route::post('', 'store')->name('admin.reset_password.store');
    });
});
