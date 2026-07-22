<?php

declare(strict_types=1);

namespace App\Enums\Compras;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoSolicitud: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Borrador = 1;
    case Pendiente = 2;
    case Aprobada = 3;
    case Rechazada = 4;
    case Cancelada = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Pendiente => 'Pendiente',
            self::Aprobada => 'Aprobada',
            self::Rechazada => 'Rechazada',
            self::Cancelada => 'Cancelada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Pendiente => 'warning',
            self::Aprobada => 'success',
            self::Rechazada => 'danger',
            self::Cancelada => 'danger',
        };
    }
}
