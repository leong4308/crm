<?php

namespace Aether\Quote\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\Quote\Models\Quote;
use Aether\Quote\Models\QuoteItem;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Quote::class,
        QuoteItem::class,
    ];
}
