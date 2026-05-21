<?php

namespace Aether\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MassUpdateRequest extends FormRequest
{
    /**
     * Determinar si la solicitud está autorizada o no.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Obtenga las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'indices' => ['required', 'array'],
            'indices.*' => ['integer'],
            'value' => ['required'],
        ];
    }
}
