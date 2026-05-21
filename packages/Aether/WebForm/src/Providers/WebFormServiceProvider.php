<?php

namespace Aether\WebForm\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class WebFormServiceProvider extends ServiceProvider
{
    /**
     * Servicios de arranque.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/routes.php');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'web_form');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'web_form');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components');

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->register(ModuleServiceProvider::class);
    }

    /**
     * Registrar servicios.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Registrar la configuración del paquete.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/menu.php', 'menu.admin');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/acl.php', 'acl');
    }
}
