<?php

namespace Aether\Automation\Providers;

use Aether\Automation\Models\Webhook;
use Aether\Automation\Models\Workflow;
use Aether\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Defina los modales a mapear con este módulo.
     *
     * @var array
     */
    protected $models = [
        Workflow::class,
        Webhook::class,
    ];
}
