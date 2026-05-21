<?php

namespace App\Enums\Compras;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoCotizacion: int implements HasColor, HasIcon, HasLabel
{
    case Activa = 0;
    case Aceptada = 1;
    case AceptadaParcial = 2;
    case Rechazada = 3;

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function getIcon(): BackedEnum
    {
        return $this->icon();
    }

    public function label(): string
    {
        return match ($this) {
            self::Activa => 'Activa',
            self::Aceptada => 'Aceptada',
            self::AceptadaParcial => 'Aceptada Parcial',
            self::Rechazada => 'Rechazada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Activa => 'gray',
            self::Aceptada => 'success',
            self::AceptadaParcial => 'warning',
            self::Rechazada => 'danger',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Activa => Heroicon::Clock,
            self::Aceptada => Heroicon::CheckBadge,
            self::AceptadaParcial => Heroicon::AdjustmentsHorizontal,
            self::Rechazada => Heroicon::NoSymbol,
        };
    }
}
