<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoAsignacion: int implements HasColor, HasIcon, HasLabel
{
    case Vigente = 1;
    case Cerrada = 2;
    case EnTransito = 3;

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
            self::Vigente => 'Vigente',
            self::Cerrada => 'Cerrada',
            self::EnTransito => 'En tránsito',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Vigente => 'success',
            self::Cerrada => 'gray',
            self::EnTransito => 'info',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Vigente => Heroicon::CheckCircle,
            self::Cerrada => Heroicon::XCircle,
            self::EnTransito => Heroicon::Truck,
        };
    }
}
