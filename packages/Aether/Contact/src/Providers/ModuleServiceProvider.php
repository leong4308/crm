<?php

namespace Aether\Contact\Providers;

use Aether\Contact\Models\Organization;
use Aether\Contact\Models\Person;
use Aether\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Person::class,
        Organization::class,
    ];
}
