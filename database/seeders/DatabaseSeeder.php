<?php

namespace Database\Seeders;

use Aether\Installer\Database\Seeders\DatabaseSeeder as AetherDatabaseSeeder;
use Illuminate\Database\Seeder;

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
