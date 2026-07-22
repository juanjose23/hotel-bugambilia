<?php

declare(strict_types=1);

namespace App\BusinessLogic\Colaboradores;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidCodigoColaborador implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('El código debe ser una cadena de texto.');

            return;
        }

        if (preg_match('/^COL-\d{4,}$/i', $value) !== 1) {
            $fail('El formato del código de colaborador no es válido (ej: COL-0001).');
        }
    }
}
