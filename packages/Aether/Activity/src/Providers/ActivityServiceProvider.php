<?php

namespace Aether\Activity\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Aether\Activity\Contracts\Activity as ActivityContract;
use Aether\Activity\Contracts\File as FileContract;
use Aether\Activity\Contracts\Participant as ParticipantContract;
use Aether\Activity\Models\Activity;
use Aether\Activity\Models\File;
use Aether\Activity\Models\Participant;

class ActivityServiceProvider extends ServiceProvider
{
    /**
     * Registrar servicios.
     *
     * Agrega enlaces de contenedores de Laravel como respaldo para que los repositorios que
     * sugerencia de tipo las interfaces del contrato se resuelven incluso si Concord
     * ModuleServiceProvider::boot() aún no se ha ejecutado.
     */
    public function register(): void
    {
        $this->app->bindIf(ActivityContract::class, Activity::class);
        $this->app->bindIf(FileContract::class, File::class);
        $this->app->bindIf(ParticipantContract::class, Participant::class);
    }

    /**
     * Servicios de arranque.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Garantizar que el registro de proxy modelo de Concord contenga la Actividad
        // enlaces después de que todos los proveedores hayan iniciado.  ModeloProxy::modelClass()
        // looks up $concord->model($contract) — a separate store from the
        // Contenedor Laravel: por lo que este registro es estrictamente necesario para
        // servidores proxy como ActivityProxy utilizados en relaciones Eloquent.
        $this->app->booted(function () {
            $concord = app('concord');

            if (! $concord->model(ActivityContract::class)) {
                $concord->registerModel(ActivityContract::class, Activity::class);
            }

            if (! $concord->model(FileContract::class)) {
                $concord->registerModel(FileContract::class, File::class);
            }

            if (! $concord->model(ParticipantContract::class)) {
                $concord->registerModel(ParticipantContract::class, Participant::class);
            }
        });
    }
}
