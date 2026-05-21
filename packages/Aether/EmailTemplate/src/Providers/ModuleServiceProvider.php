<?php

namespace Aether\EmailTemplate\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\EmailTemplate\Models\EmailTemplate;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        EmailTemplate::class,
    ];
}
