<?php

namespace Aether\Marketing\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\Marketing\Models\Campaign;
use Aether\Marketing\Models\Event;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Defina la matriz del módulo.
     *
     * @var array
     */
    protected $models = [
        Event::class,
        Campaign::class,
    ];
}
