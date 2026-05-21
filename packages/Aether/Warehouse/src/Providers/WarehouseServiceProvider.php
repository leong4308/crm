<?php

namespace Aether\Warehouse\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class WarehouseServiceProvider extends ServiceProvider
{
    /**
     * Servicios de arranque.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    /**
     * Registrar servicios.
     *
     * @return void
     */
    public function register() {}
}
