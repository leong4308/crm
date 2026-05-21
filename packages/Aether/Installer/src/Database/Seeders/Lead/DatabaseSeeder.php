<?php

namespace Aether\Installer\Database\Seeders\Lead;

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
        $this->call(PipelineSeeder::class, false, ['parameters' => $parameters]);
        $this->call(TypeSeeder::class, false, ['parameters' => $parameters]);
        $this->call(SourceSeeder::class, false, ['parameters' => $parameters]);
    }
}
