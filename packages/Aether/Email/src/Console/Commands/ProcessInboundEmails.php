<?php

namespace Aether\Email\Console\Commands;

use Aether\Email\InboundEmailProcessor\Contracts\InboundEmailProcessor;
use Illuminate\Console\Command;

class ProcessInboundEmails extends Command
{
    /**
     * El nombre y la firma del comando de la consola.
     *
     * @var string
     */
    protected $signature = 'inbound-emails:process';

    /**
     * La descripción del comando de la consola.
     *
     * @var string
     */
    protected $description = 'This command will process the incoming emails from the mail server.';

    /**
     * Cree una nueva instancia de comando.
     *
     * @return void
     */
    public function __construct(
        protected InboundEmailProcessor $inboundEmailProcessor
    ) {
        parent::__construct();
    }

    /**
     * Manejar.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Processing the incoming emails.');

        $this->inboundEmailProcessor->processMessagesFromAllFolders();

        $this->info('Incoming emails processed successfully.');
    }
}
