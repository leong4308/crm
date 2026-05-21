<?php

namespace Aether\DataTransfer\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\DataTransfer\Models\Import;
use Aether\DataTransfer\Models\ImportBatch;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Defina modelos para mapear con interfaces de repositorio.
     *
     * @var array
     */
    protected $models = [
        Import::class,
        ImportBatch::class,
    ];
}
