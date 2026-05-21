<?php

namespace Aether\Tag\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\Tag\Models\Tag;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Tag::class,
    ];
}
