<?php

namespace Aether\Email\Providers;

use Aether\Core\Providers\BaseModuleServiceProvider;
use Aether\Email\Models\Attachment;
use Aether\Email\Models\Email;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Email::class,
        Attachment::class,
    ];
}
