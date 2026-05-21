<?php

namespace Aether\Product\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\Product\Models\Product;
use Aether\Product\Models\ProductInventory;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Product::class,
        ProductInventory::class,
    ];
}
