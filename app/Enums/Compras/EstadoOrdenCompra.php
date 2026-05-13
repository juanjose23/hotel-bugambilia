<?php

namespace App\Enums\Compras;

use Filament\Support\Icons\Heroicon;

enum EstadoOrdenCompra: int
{
    case Borrador = 1;
    case Emitida = 2;
    case EnTransito = 3;
    case Recibida = 4;
    case Cancelada = 5;

    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Emitida => 'Emitida',
            self::EnTransito => 'En Tránsito',
            self::Recibida => 'Recibida',
            self::Cancelada => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Emitida => 'info',
            self::EnTransito => 'warning',
            self::Recibida => 'success',
            self::Cancelada => 'danger',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Borrador => Heroicon::DocumentPlus,
            self::Emitida => Heroicon::PaperAirplane,
            self::EnTransito => Heroicon::Truck,
            self::Recibida => Heroicon::CheckBadge,
            self::Cancelada => Heroicon::XMark,
        };
    }
}
