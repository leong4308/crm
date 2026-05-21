<?php

namespace Aether\Warehouse\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\Warehouse\Models\Location;
use Aether\Warehouse\Models\Warehouse;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Location::class,
        Warehouse::class,
    ];
}
