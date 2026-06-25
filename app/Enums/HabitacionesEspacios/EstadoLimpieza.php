<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use App\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoLimpieza: int implements HasColor, HasIcon, HasLabel
{
    use HasEnumHelpers;

    case Pendiente = 1;
    case EnProgreso = 2;
    case Completada = 3;
    case CompletadaConDiscrepancia = 4;

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnProgreso => 'En Progreso',
            self::Completada => 'Completada',
            self::CompletadaConDiscrepancia => 'Completada con Discrepancia',
        };
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::EnProgreso => 'primary',
            self::Completada => 'success',
            self::CompletadaConDiscrepancia => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pendiente => Heroicon::Clock,
            self::EnProgreso => Heroicon::ArrowPath,
            self::Completada => Heroicon::CheckCircle,
            self::CompletadaConDiscrepancia => Heroicon::ExclamationTriangle,
        };
    }
}
