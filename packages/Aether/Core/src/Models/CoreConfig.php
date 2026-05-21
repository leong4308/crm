<?php

namespace Aether\Core\Models;

use Aether\Core\Contracts\CoreConfig as CoreConfigContract;
use Illuminate\Database\Eloquent\Model;

class CoreConfig extends Model implements CoreConfigContract
{
    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $table = 'core_config';

    protected $fillable = [
        'code',
        'value',
        'locale',
    ];

    protected $hidden = ['token'];
}
