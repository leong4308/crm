<?php

namespace Aether\Core\Console\Commands;

use Illuminate\Console\Command;

class Version extends Command
{
    /**
     * El nombre y la firma del comando de la consola.
     *
     * @var string
     */
    protected $signature = 'aether-crm:version';

    /**
     * La descripción del comando de la consola.
     *
     * @var string
     */
    protected $description = 'Displays current version of CRM v1 CRM installed';

    /**
     * Cree una nueva instancia de comando.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Ejecute el comando de la consola.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('v'.core()->version());
    }
}
