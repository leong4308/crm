<?php

namespace Aether\DataGrid\Providers;

use Illuminate\Support\ServiceProvider;

class DataGridServiceProvider extends ServiceProvider
{
    /**
     * Arranque cualquier servicio de aplicación.
     */
    public function boot(): void
    {
        include __DIR__.'/../Http/helpers.php';

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    /**
     * Registre cualquier servicio de aplicación.
     */
    public function register(): void {}
}
