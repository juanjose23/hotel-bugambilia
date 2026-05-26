<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoIndividualizacion: int implements HasColor, HasIcon, HasLabel
{
    case Pendiente = 1;
    case EnProceso = 2;
    case Completado = 3;

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
            self::Pendiente => 'Pendiente',
            self::EnProceso => 'En proceso',
            self::Completado => 'Completado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::EnProceso => 'info',
            self::Completado => 'success',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Pendiente => Heroicon::Clock,
            self::EnProceso => Heroicon::ArrowPath,
            self::Completado => Heroicon::CheckCircle,
        };
    }
}
