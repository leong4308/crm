<?php

namespace Aether\Core\Providers;

use Aether\Core\Models\CoreConfig;
use Aether\Core\Models\Country;
use Aether\Core\Models\CountryState;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        CoreConfig::class,
        Country::class,
        CountryState::class,
    ];
}
