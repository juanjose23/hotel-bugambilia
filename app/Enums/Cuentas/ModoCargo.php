<?php

declare(strict_types=1);

namespace App\Enums\Cuentas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ModoCargo: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Porcentaje = 1;
    case MontoFijo = 2;
    case Manual = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Porcentaje => 'Porcentaje',
            self::MontoFijo => 'Monto Fijo',
            self::Manual => 'Manual',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Porcentaje => 'info',
            self::MontoFijo => 'success',
            self::Manual => 'warning',
        };
    }
}
