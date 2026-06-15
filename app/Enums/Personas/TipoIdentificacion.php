<?php

declare(strict_types=1);

namespace App\Enums\Personas;

use App\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipoIdentificacion: string implements HasColor, HasLabel
{
    use HasEnumHelpers;

    case Cedula = 'cedula';
    case Dni = 'dni';
    case Pasaporte = 'pasaporte';
    case Residencia = 'residencia';
    case Nit = 'nit';
    case Ruc = 'ruc';
    case Otro = 'otro';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cedula => 'Cédula',
            self::Dni => 'DNI',
            self::Pasaporte => 'Pasaporte',
            self::Residencia => 'Residencia',
            self::Nit => 'NIT',
            self::Ruc => 'RUC',
            self::Otro => 'Otro',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Cedula => 'success',
            self::Dni => 'danger',
            self::Pasaporte => 'warning',
            self::Residencia, self::Otro => 'info',
            self::Nit => 'primary',
            self::Ruc => 'secondary',
        };
    }
}
