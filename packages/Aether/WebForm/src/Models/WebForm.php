<?php

namespace Aether\WebForm\Models;

use Illuminate\Database\Eloquent\Model;
use Aether\WebForm\Contracts\WebForm as WebFormContract;

class WebForm extends Model implements WebFormContract
{
    protected $fillable = [
        'form_id',
        'title',
        'description',
        'submit_button_label',
        'submit_success_action',
        'submit_success_content',
        'create_lead',
        'background_color',
        'form_background_color',
        'form_title_color',
        'form_submit_button_color',
        'attribute_label_color',
    ];

    /**
     * Los atributos que pertenecen a la actividad.
     */
    public function attributes()
    {
        return $this->hasMany(WebFormAttributeProxy::modelClass());
    }
}
