<?php

namespace Aether\User\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\User\Models\Group;
use Aether\User\Models\Role;
use Aether\User\Models\User;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Group::class,
        Role::class,
        User::class,
    ];
}
