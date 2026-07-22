<?php

declare(strict_types=1);

namespace App\Enums\Compras;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoCotizacion: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Borrador = 0;
    case Activa = 1;
    case Aceptada = 3;
    case Rechazada = 4;
    case Cancelada = 5;
    case AceptadaParcial = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Activa => 'Activa',
            self::Aceptada => 'Aceptada',
            self::Rechazada => 'Rechazada',
            self::Cancelada => 'Cancelada',
            self::AceptadaParcial => 'Aceptada Parcial',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Activa => 'info',
            self::Aceptada => 'success',
            self::Rechazada => 'danger',
            self::Cancelada => 'danger',
            self::AceptadaParcial => 'warning',
        };
    }
}
