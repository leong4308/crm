<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Aether\Installer\Database\Seeders\DatabaseSeeder as AetherDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AetherDatabaseSeeder::class);
    }
}
