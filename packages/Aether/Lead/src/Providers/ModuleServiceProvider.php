<?php

namespace Aether\Lead\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\Lead\Models\Lead;
use Aether\Lead\Models\Pipeline;
use Aether\Lead\Models\Product;
use Aether\Lead\Models\Source;
use Aether\Lead\Models\Stage;
use Aether\Lead\Models\Type;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Lead::class,
        Pipeline::class,
        Product::class,
        Source::class,
        Stage::class,
        Type::class,
    ];
}
