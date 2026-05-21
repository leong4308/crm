<?php

namespace Aether\Automation\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Aether\Automation\Listeners\Entity;

class WorkflowServiceProvider extends ServiceProvider
{
    /**
     * Servicios de arranque.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        Event::listen('*', function ($eventName, array $data) {
            if (! in_array($eventName, data_get(config('workflows.trigger_entities'), '*.events.*.event'))) {
                return;
            }

            app(Entity::class)->process($eventName, current($data));
        });
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
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/workflows.php', 'workflows');
    }
}
