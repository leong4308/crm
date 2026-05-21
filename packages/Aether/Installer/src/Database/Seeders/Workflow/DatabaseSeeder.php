<?php

namespace Aether\Installer\Database\Seeders\Workflow;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Sembra la base de datos de la aplicación.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        $this->call(WorkflowSeeder::class, false, ['parameters' => $parameters]);
    }
}
