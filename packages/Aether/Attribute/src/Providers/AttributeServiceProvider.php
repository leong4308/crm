<?php

namespace Aether\Attribute\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class AttributeServiceProvider extends ServiceProvider
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
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Registrar la configuración del paquete.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/attribute_lookups.php', 'attribute_lookups'
        );
    }
}
