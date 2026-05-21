<?php

namespace Aether\Attribute\Providers;

use Aether\Attribute\Models\Attribute;
use Aether\Attribute\Models\AttributeOption;
use Aether\Attribute\Models\AttributeValue;
use Aether\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * @var array{
     *  0: cadena de clase <Atributo>,
     *  1: cadena de clase <Opción de atributo>,
     *  2: cadena de clase <Valor de atributo>
     * }
     */
    protected $models = [
        Attribute::class,
        AttributeOption::class,
        AttributeValue::class,
    ];
}
