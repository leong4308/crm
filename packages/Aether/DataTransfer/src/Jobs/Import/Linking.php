<?php

namespace Aether\DataTransfer\Jobs\Import;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Aether\DataTransfer\Helpers\Import as ImportHelper;

class Linking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Cree una nueva instancia de trabajo.
     *
     * @param  mixed  $import
     * @return void
     */
    public function __construct(protected $import)
    {
        $this->import = $import;
    }

    /**
     * Ejecutar el trabajo.
     *
     * @return void
     */
    public function handle()
    {
        app(ImportHelper::class)
            ->setImport($this->import)
            ->linking();
    }
}
