<?php

namespace Aether\Core\Contracts\Validations;

use Illuminate\Contracts\Validation\Rule;

class Code implements Rule
{
    /**
     * Determine si se aprueba la regla de validación.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/^[a-zA-Z]+[a-zA-Z0-9_]+$/', $value);
    }

    /**
     * Recibe el mensaje de error de validación.
     *
     * @return string
     */
    public function message()
    {
        return trans('core::app.validations.code');
    }
}
