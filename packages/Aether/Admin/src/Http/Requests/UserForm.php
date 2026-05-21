<?php

namespace Aether\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserForm extends FormRequest
{
    protected $rules;

    /**
     * Determine si el usuario está autorizado a realizar esta solicitud.
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
        $this->rules = [
            'name' => 'required',
            'email' => 'email|unique:users,email',
            'password' => 'nullable',
            'password_confirmation' => 'nullable|required_with:password|same:password',
            'status' => 'sometimes',
            'role_id' => 'required',
        ];

        if ($this->method() == 'PUT') {
            $this->rules['email'] = 'email|unique:users,email,'.$this->route('id');
        }

        return $this->rules;
    }
}
