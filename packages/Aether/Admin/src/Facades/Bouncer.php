<?php

namespace Aether\Admin\Facades;

use Illuminate\Support\Facades\Facade;

class Bouncer extends Facade
{
    /**
     * Obtenga el nombre registrado del componente.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'bouncer';
    }
}
