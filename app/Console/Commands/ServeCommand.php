<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Symfony\Component\Process\Process;

class ServeCommand extends BaseServeCommand
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serve the application and start WhatsApp API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('<info>Iniciando entorno de desarrollo...</info>');

        $port = $this->option('port') ?: 8000;
        $host = $this->option('host') ?: '127.0.0.1';

        // Iniciar el servidor de WhatsApp usando Process
        $whatsappPath = base_path('OpenWA');
        $whatsappProcess = Process::fromShellCommandline('npm run dev', $whatsappPath);
        $whatsappProcess->setTimeout(null);
        $whatsappProcess->start(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        $this->output->writeln('  <fg=green;options=bold>➜</>  <options=bold>CRM Aether:</>  <fg=cyan>http://'.$host.':'.$port.'/admin</>');
        $this->output->writeln('  <fg=green;options=bold>➜</>  <options=bold>OpenWA Dashboard:</> <fg=cyan>http://localhost:2886</>');
        $this->output->writeln('  <fg=green;options=bold>➜</>  <options=bold>OpenWA API:</> <fg=cyan>http://localhost:2785/api/docs</>');
        $this->line('');

        // Registrar una función de cierre para matar el proceso de Node.js
        register_shutdown_function(function () use ($whatsappProcess) {
            if ($whatsappProcess->isRunning()) {
                $whatsappProcess->stop();
            }
        });

        return parent::handle();
    }
}
