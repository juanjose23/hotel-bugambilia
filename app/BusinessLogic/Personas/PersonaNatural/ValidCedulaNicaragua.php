<?php

declare(strict_types=1);

namespace App\BusinessLogic\Personas\PersonaNatural;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidCedulaNicaragua implements ValidationRule
{
    private const string LETRAS = 'ABCDEFGHJKLMNPQRSTUVWXY';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valStr = is_scalar($value) ? (string) $value : '';
        $valueStr = strtoupper(trim($valStr));

        if ($valueStr === '') {
            return;
        }

        if (! $this->validarFormato($valueStr)) {
            $fail('El formato de la cédula no es válido. Ejemplo: 001-010102-1234G');

            return;
        }

        $cedulaLimpia = str_replace('-', '', $valueStr);

        if (! $this->validarMunicipio($cedulaLimpia)) {
            $fail('El código de municipio de la cédula no es válido.');

            return;
        }

        if (! $this->validarFecha($cedulaLimpia)) {
            $fail('La fecha de nacimiento en la cédula no es válida.');

            return;
        }

        if (! $this->validarLetra($cedulaLimpia)) {
            $fail('La letra de verificación de la cédula no es correcta.');
        }
    }

    public function validarFormato(string $valueStr): bool
    {
        return preg_match('/^\d{3}-?\d{6}-?\d{4}[A-Z]$/i', $valueStr) === 1;
    }

    public function validarMunicipio(string $cedulaLimpia): bool
    {
        $municipio = substr($cedulaLimpia, 0, 3);
        $num = (int) $municipio;

        return $num >= 1 && $num <= 100;
    }

    public function validarFecha(string $cedulaLimpia): bool
    {
        $day = (int) substr($cedulaLimpia, 3, 2);
        $month = (int) substr($cedulaLimpia, 5, 2);
        $year = (int) substr($cedulaLimpia, 7, 2);

        $fullYear = $year > 30 ? 1900 + $year : 2000 + $year;

        return checkdate($month, $day, $fullYear);
    }

    public function validarLetra(string $cedulaLimpia): bool
    {
        $numero = (int) substr($cedulaLimpia, 0, 13);
        $letraEsperada = substr($cedulaLimpia, 13, 1);

        $posicion = $numero % 23;
        $letraCalculada = self::LETRAS[$posicion];

        return $letraEsperada === $letraCalculada;
    }
}
