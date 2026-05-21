<?php

namespace Aether\Marketing\Console\Commands;

use Illuminate\Console\Command;
use Aether\Marketing\Helpers\Campaign;

class CampaignCommand extends Command
{
    /**
     * El nombre y la firma del comando de la consola.
     *
     * @var string
     */
    protected $signature = 'campaign:process';

    /**
     * La descripción del comando de la consola.
     *
     * @var string
     */
    protected $description = 'Process campaigns and send emails to the contact persons.';

    /**
     * Cree una nueva instancia de comando.
     *
     * @return void
     */
    public function __construct(protected Campaign $campaignHelper)
    {
        parent::__construct();
    }

    /**
     * Ejecute el comando de la consola.
     */
    public function handle()
    {
        $this->info('🚀 Starting campaign processing...');

        try {
            $this->campaignHelper->process();

            $this->info('✅ Campaign processing completed successfully!');
        } catch (\Exception $e) {
            $this->error('❌ An error occurred during campaign processing: '.$e->getMessage());
        }
    }
}
