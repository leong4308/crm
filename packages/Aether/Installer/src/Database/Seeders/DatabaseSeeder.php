<?php

namespace Aether\Installer\Database\Seeders;

use Aether\Installer\Database\Seeders\Attribute\DatabaseSeeder as AttributeSeeder;
use Aether\Installer\Database\Seeders\Core\DatabaseSeeder as CoreSeeder;
use Aether\Installer\Database\Seeders\EmailTemplate\DatabaseSeeder as EmailTemplateSeeder;
use Aether\Installer\Database\Seeders\Lead\DatabaseSeeder as LeadSeeder;
use Aether\Installer\Database\Seeders\User\DatabaseSeeder as UserSeeder;
use Aether\Installer\Database\Seeders\Workflow\DatabaseSeeder as WorkflowSeeder;
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
        $this->call(AttributeSeeder::class, false, ['parameters' => $parameters]);
        $this->call(CoreSeeder::class, false, ['parameters' => $parameters]);
        $this->call(EmailTemplateSeeder::class, false, ['parameters' => $parameters]);
        $this->call(LeadSeeder::class, false, ['parameters' => $parameters]);
        $this->call(UserSeeder::class, false, ['parameters' => $parameters]);
        $this->call(WorkflowSeeder::class, false, ['parameters' => $parameters]);
    }
}
