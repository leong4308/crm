<?php

namespace Aether\Attribute\Models;

use Aether\Attribute\Contracts\AttributeOption as AttributeOptionContract;
use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model implements AttributeOptionContract
{
    public $timestamps = false;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sort_order',
        'attribute_id',
    ];

    /**
     * Obtenga el atributo propietario de la opción de atributo.
     */
    public function attribute()
    {
        return $this->belongsTo(AttributeProxy::modelClass());
    }
}
