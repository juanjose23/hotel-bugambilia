<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoMantenimiento: int implements HasColor, HasIcon, HasLabel
{
    case Programado = 1;
    case EnProceso = 2;
    case Completado = 3;
    case Cancelado = 4;

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
            self::Programado => 'Programado',
            self::EnProceso => 'En proceso',
            self::Completado => 'Completado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Programado => 'info',
            self::EnProceso => 'warning',
            self::Completado => 'success',
            self::Cancelado => 'danger',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Programado => Heroicon::Calendar,
            self::EnProceso => Heroicon::ArrowPath,
            self::Completado => Heroicon::CheckCircle,
            self::Cancelado => Heroicon::XCircle,
        };
    }
}
