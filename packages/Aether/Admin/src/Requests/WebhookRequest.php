<?php

namespace Aether\Admin\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebhookRequest extends FormRequest
{
    /**
     * Determine si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtenga las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'entity_type' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'method' => 'required|string|max:255',
            'end_point' => 'required|string|max:255',
            'query_params' => 'nullable',
            'headers' => 'nullable',
            'payload_type' => [
                'required',
                'string',
                'max:255',
                Rule::in(['default', 'x-www-form-urlencoded', 'raw']),
            ],
            'raw_payload_type' => [
                'string',
                'max:255',
                Rule::in(['json', 'text']),
            ],
            'payload' => 'nullable',
        ];
    }
}
