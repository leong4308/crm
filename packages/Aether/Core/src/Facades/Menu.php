<?php

namespace Aether\Core\Facades;

use Illuminate\Support\Facades\Facade;

class Menu extends Facade
{
    /**
     * Obtenga el nombre registrado del componente.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'menu';
    }
}
