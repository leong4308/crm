<?php

namespace Aether\Installer\Database\Seeders\Core;

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
        $this->call(CountriesSeeder::class, false, ['parameters' => $parameters]);
        $this->call(StatesSeeder::class, false, ['parameters' => $parameters]);
    }
}
