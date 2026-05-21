<?php

namespace Aether\Lead\Models;

use Aether\Lead\Contracts\Type as TypeContract;
use Illuminate\Database\Eloquent\Model;

class Type extends Model implements TypeContract
{
    protected $table = 'lead_types';

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Obtenga las pistas.
     */
    public function leads()
    {
        return $this->hasMany(LeadProxy::modelClass());
    }
}
