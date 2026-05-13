<?php

namespace App\Enums\Compras;

use Filament\Support\Icons\Heroicon;

enum EstadoSolicitud: int
{
    case Borrador = 1;
    case Pendiente = 2;
    case Aprobada = 3;
    case Rechazada = 4;
    case Cancelada = 5;

    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Pendiente => 'Pendiente',
            self::Aprobada => 'Aprobada',
            self::Rechazada => 'Rechazada',
            self::Cancelada => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Pendiente => 'warning',
            self::Aprobada => 'success',
            self::Rechazada => 'danger',
            self::Cancelada => 'danger',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Borrador => Heroicon::DocumentText,
            self::Pendiente => Heroicon::Clock,
            self::Aprobada => Heroicon::CheckCircle,
            self::Rechazada => Heroicon::NoSymbol,
            self::Cancelada => Heroicon::XCircle,
        };
    }
}
