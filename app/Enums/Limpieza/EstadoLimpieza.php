<?php

declare(strict_types=1);

namespace App\Enums\Limpieza;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoLimpieza: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Pendiente = 1;
    case EnProgreso = 2;
    case Completada = 3;
    case CompletadaConDiscrepancia = 4;
    case Cancelada = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnProgreso => 'En Progreso',
            self::Completada => 'Completada',
            self::CompletadaConDiscrepancia => 'Completada con Discrepancia',
            self::Cancelada => 'Cancelada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::EnProgreso => 'info',
            self::Completada => 'success',
            self::CompletadaConDiscrepancia => 'danger',
            self::Cancelada => 'gray',
        };
    }

    public function estaActiva(): bool
    {
        return in_array($this, [self::Pendiente, self::EnProgreso], strict: true);
    }

    public function estaFinalizada(): bool
    {
        return in_array($this, [self::Completada, self::CompletadaConDiscrepancia], strict: true);
    }
}
