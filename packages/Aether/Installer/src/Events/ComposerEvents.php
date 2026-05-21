<?php

namespace Aether\Installer\Events;

use Symfony\Component\Console\Output\ConsoleOutput;

class ComposerEvents
{
    /**
     * Publicar crear proyecto.
     *
     * @return void
     */
    public static function postCreateProject()
    {
        $output = new ConsoleOutput;

        $output->writeln(file_get_contents(__DIR__.'/../Templates/on-boarding.php'));
    }
}
