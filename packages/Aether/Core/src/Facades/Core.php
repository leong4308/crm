<?php

namespace Aether\Core\Facades;

use Illuminate\Support\Facades\Facade;

class Core extends Facade
{
    /**
     * Obtenga el nombre registrado del componente.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'core';
    }
}
