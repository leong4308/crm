<?php

namespace Aether\WebForm\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneNumber implements Rule
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
        // Esta expresión regular permite números de teléfono con las siguientes condiciones:
        // - El número de teléfono puede comenzar con un signo "+" opcional.
        // - Después del signo "+", debe haber uno o más dígitos.
        return preg_match('/^\+?\d+$/', $value);
    }

    /**
     * Recibe el mensaje de error de validación.
     *
     * @return string
     */
    public function message()
    {
        return trans('web_form::app.validations.invalid-phone-number');
    }
}
