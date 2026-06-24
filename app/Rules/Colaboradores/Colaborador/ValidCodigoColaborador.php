<?php

namespace App\Rules\Colaboradores\Colaborador;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCodigoColaborador implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valStr = is_scalar($value) ? (string) $value : '';
        if (! preg_match('/^COL-\d{4}$/', $valStr)) {
            $fail('El código del colaborador debe tener el formato COL-0001.');
        }
    }
}
