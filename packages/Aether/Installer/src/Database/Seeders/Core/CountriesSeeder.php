<?php

namespace Aether\Installer\Database\Seeders\Core;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesSeeder extends Seeder
{
    /**
     * Sembra la base de datos de la aplicación.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        DB::table('countries')->delete();

        $countries = json_decode(file_get_contents(__DIR__.'/../../../Data/countries.json'), true);

        DB::table('countries')->insert($countries);
    }
}
