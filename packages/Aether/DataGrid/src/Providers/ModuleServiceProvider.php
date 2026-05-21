<?php

namespace Aether\DataGrid\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\DataGrid\Models\SavedFilter;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        SavedFilter::class,
    ];
}
