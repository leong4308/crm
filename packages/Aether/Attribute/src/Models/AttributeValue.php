<?php

namespace Aether\Attribute\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\Activity\Traits\LogsActivity;
use Aether\Attribute\Contracts\AttributeValue as AttributeValueContract;

class AttributeValue extends Model implements AttributeValueContract
{
    use LogsActivity;

    /**
     * Deshabilite las marcas de tiempo predeterminadas.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Transfiera los atributos a sus respectivos tipos.
     *
     * @var array
     */
    protected $casts = [
        'json_value' => 'array',
    ];

    /**
     * Los atributos que se pueden completar para el modelo.
     *
     * @var array
     */
    protected $fillable = [
        'attribute_id',
        'text_value',
        'boolean_value',
        'integer_value',
        'float_value',
        'datetime_value',
        'date_value',
        'json_value',
        'entity_id',
        'entity_type',
    ];

    /**
     * Los atributos que se utilizan para registrar la actividad.
     *
     * @var array
     */
    public static $attributeTypeFields = [
        'text' => 'text_value',
        'textarea' => 'text_value',
        'price' => 'float_value',
        'boolean' => 'boolean_value',
        'select' => 'integer_value',
        'multiselect' => 'text_value',
        'checkbox' => 'text_value',
        'email' => 'json_value',
        'address' => 'json_value',
        'phone' => 'json_value',
        'lookup' => 'integer_value',
        'datetime' => 'datetime_value',
        'date' => 'date_value',
        'file' => 'text_value',
        'image' => 'text_value',
    ];

    /**
     * Obtenga el atributo propietario del valor del atributo.
     */
    public function attribute()
    {
        return $this->belongsTo(AttributeProxy::modelClass());
    }

    /**
     * Obtenga el modelo de entidad matriz (clientes potenciales, productos, personas u organizaciones).
     */
    public function entity()
    {
        return $this->morphTo();
    }
}
