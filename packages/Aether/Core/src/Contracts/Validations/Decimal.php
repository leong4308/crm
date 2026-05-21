<?php

namespace Aether\Core\Contracts\Validations;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Decimal implements ValidationRule
{
    /**
     * Ejecute la regla de validación.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^\d*(\.\d{1,4})?$/', $value)) {
            $fail(trans('admin::app.validations.message.decimal', ['attribute' => $attribute]));
        }
    }
}
