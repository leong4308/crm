<?php

namespace Aether\WebForm\Models;

use Aether\Attribute\Models\AttributeProxy;
use Aether\WebForm\Contracts\WebFormAttribute as WebFormAttributeContract;
use Illuminate\Database\Eloquent\Model;

class WebFormAttribute extends Model implements WebFormAttributeContract
{
    /**
     * Indica si el modelo debe tener una marca de tiempo.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'placeholder',
        'is_required',
        'is_hidden',
        'sort_order',
        'attribute_id',
        'web_form_id',
    ];

    /**
     * Obtenga el atributo propietario del atributo.
     */
    public function attribute()
    {
        return $this->belongsTo(AttributeProxy::modelClass());
    }

    /**
     * Obtenga el formulario web propietario del atributo.
     */
    public function web_form()
    {
        return $this->belongsTo(WebFormProxy::modelClass());
    }
}
