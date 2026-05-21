<?php

namespace Aether\Email\Providers;

use Aether\Email\Console\Commands\ProcessInboundEmails;
use Aether\Email\InboundEmailProcessor\Contracts\InboundEmailProcessor;
use Aether\Email\InboundEmailProcessor\SendgridEmailProcessor;
use Aether\Email\InboundEmailProcessor\WebklexImapEmailProcessor;
use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Servicios de arranque.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->app->bind(InboundEmailProcessor::class, function ($app) {
            $driver = config('mail-receiver.default');

            if ($driver === 'sendgrid') {
                return $app->make(SendgridEmailProcessor::class);
            }

            if ($driver === 'webklex-imap') {
                return $app->make(WebklexImapEmailProcessor::class);
            }

            throw new \Exception("Unsupported mail receiver driver [{$driver}].");
        });
    }

    /**
     * Registrar servicios.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands();
    }

    /**
     * Registre los comandos de la consola de este paquete.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessInboundEmails::class,
            ]);
        }
    }
}
