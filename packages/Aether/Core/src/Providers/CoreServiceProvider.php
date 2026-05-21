<?php

namespace Aether\Core\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Aether\Core\Acl;
use Aether\Core\Console\Commands\Version;
use Aether\Core\Core;
use Aether\Core\Facades\Acl as AclFacade;
use Aether\Core\Facades\Core as CoreFacade;
use Aether\Core\Facades\Menu as MenuFacade;
use Aether\Core\Facades\SystemConfig as SystemConfigFacade;
use Aether\Core\Menu;
use Aether\Core\SystemConfig;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Servicios de arranque.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function boot()
    {
        include __DIR__.'/../Http/helpers.php';

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'core');

        $this->publishes([
            dirname(__DIR__).'/Config/concord.php' => config_path('concord.php'),
            dirname(__DIR__).'/Config/cors.php' => config_path('cors.php'),
            dirname(__DIR__).'/Config/sanctum.php' => config_path('sanctum.php'),
        ]);
    }

    /**
     * Registrar servicios.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands();

        $this->registerFacades();
    }

    /**
     * Registre a Bouncer como singleton.
     *
     * @return void
     */
    protected function registerFacades()
    {
        $loader = AliasLoader::getInstance();

        $loader->alias('acl', AclFacade::class);

        $loader->alias('core', CoreFacade::class);

        $loader->alias('system_config', SystemConfigFacade::class);

        $loader->alias('menu', MenuFacade::class);

        $this->app->singleton('acl', fn () => app(Acl::class));

        $this->app->singleton('core', fn () => app(Core::class));

        $this->app->singleton('system_config', fn () => app()->make(SystemConfig::class));

        $this->app->singleton('menu', fn () => app()->make(Menu::class));
    }

    /**
     * Registre los comandos de la consola de este paquete.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Version::class,
            ]);
        }
    }
}
