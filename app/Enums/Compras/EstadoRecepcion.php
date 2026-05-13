<?php

namespace App\Enums\Compras;

use Filament\Support\Icons\Heroicon;

enum EstadoRecepcion: int
{
    case Completa = 1;
    case Parcial = 2;
    case ConDiscrepancia = 3;
    case Rechazada = 4;
    case EnCuarentena = 5;

    public function label(): string
    {
        return match ($this) {
            self::Completa => 'Completa',
            self::Parcial => 'Parcial',
            self::ConDiscrepancia => 'Con Discrepancia',
            self::Rechazada => 'Rechazada',
            self::EnCuarentena => 'En Cuarentena',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Completa => 'success',
            self::Parcial => 'warning',
            self::ConDiscrepancia => 'orange',
            self::Rechazada => 'danger',
            self::EnCuarentena => 'info',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Completa => Heroicon::CheckBadge,
            self::Parcial => Heroicon::Clock,
            self::ConDiscrepancia => Heroicon::ExclamationTriangle,
            self::Rechazada => Heroicon::NoSymbol,
            self::EnCuarentena => Heroicon::ShieldCheck,
        };
    }
}
