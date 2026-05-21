<?php

namespace Aether\Core\Exceptions;

class ViterNotFound extends \Exception
{
    /**
     * Crea una instancia.
     *
     * @param  string  $theme
     * @return void
     */
    public function __construct($namespace)
    {
        parent::__construct("Viter with `$namespace` namespace not found. Please add `$namespace` namespace in the `config/aether-vite.php` file.", 1);
    }
}
