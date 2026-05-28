<?php

declare(strict_types=1);

namespace App\Enums\Compras;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoSolicitud: int implements HasColor, HasIcon, HasLabel
{
    case Borrador = 1;
    case Pendiente = 2;
    case Aprobada = 3;
    case Rechazada = 4;
    case Cancelada = 5;

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
