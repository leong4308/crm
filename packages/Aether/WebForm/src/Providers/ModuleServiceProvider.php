<?php

namespace Aether\WebForm\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\WebForm\Models\WebForm;
use Aether\WebForm\Models\WebFormAttribute;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        WebForm::class,
        WebFormAttribute::class,
    ];
}
