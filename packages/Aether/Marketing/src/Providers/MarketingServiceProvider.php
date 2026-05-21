<?php

namespace Aether\Marketing\Providers;

use Aether\Marketing\Console\Commands\CampaignCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class MarketingServiceProvider extends ServiceProvider
{
    /**
     * Servicios de arranque.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('campaign:process')->daily();
        });
    }

    /**
     * Registrar servicios.
     */
    public function register(): void
    {
        $this->registerCommands();

        $this->app->register(ModuleServiceProvider::class);
    }

    /**
     * Registre los comandos.
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CampaignCommand::class,
            ]);
        }
    }
}
