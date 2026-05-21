<?php

namespace Aether\Activity\Providers;

use Aether\Activity\Models\Activity;
use Aether\Activity\Models\File;
use Aether\Activity\Models\Participant;
use Aether\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Activity::class,
        File::class,
        Participant::class,
    ];
}
