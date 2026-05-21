<?php

use Aether\WebForm\Http\Controllers\WebFormController;
use Illuminate\Support\Facades\Route;

Route::controller(WebFormController::class)->middleware(['web', 'admin_locale'])->prefix('web-forms')->group(function () {
    Route::get('forms/{id}/form.js', 'formJS')->name('admin.settings.web_forms.form_js');

    Route::get('forms/{id}/form.html', 'preview')->name('admin.settings.web_forms.preview');

    Route::post('forms/{id}', 'formStore')->name('admin.settings.web_forms.form_store');

    Route::group(['middleware' => ['user']], function () {
        Route::get('form/{id}/form.html', 'view')->name('admin.settings.web_forms.view');
    });
});
