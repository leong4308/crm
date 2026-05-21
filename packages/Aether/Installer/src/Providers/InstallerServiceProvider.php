<?php

namespace Aether\Installer\Providers;

use Aether\Installer\Console\Commands\Installer as InstallerCommand;
use Aether\Installer\Http\Middleware\CanInstall;
use Aether\Installer\Http\Middleware\Locale;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class InstallerServiceProvider extends ServiceProvider
{
    /**
     * Indica si se aplaza la carga del proveedor.
     */
    protected bool $defer = false;

    /**
     * Inicia los eventos de la aplicación.
     */
    public function boot(Router $router): void
    {
        $router->middlewareGroup('install', [CanInstall::class]);

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'installer');

        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'installer');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'installer');

        $router->aliasMiddleware('installer_locale', Locale::class);

        Event::listen('aether.installed', 'Aether\Installer\Listeners\Installer@installed');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'installer');

        /**
         * Ruta para acceder al archivo de imagen aplicado de plantilla
         */
        $this->app['router']->get('cache/{filename}', [
            'uses' => 'Aether\Installer\Http\Controllers\ImageCacheController@getImage',
            'as' => 'image_cache',
        ])->where(['filename' => '[ \w\\.\\/\\-\\@\(\)\=]+']);
    }

    /**
     * Registre el proveedor de servicios.
     */
    public function register(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([InstallerCommand::class]);
    }
}
