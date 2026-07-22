<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoMantenimiento: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Programado = 1;
    case EnProceso = 2;
    case Completado = 3;
    case Cancelado = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Programado => 'Programado',
            self::EnProceso => 'En Proceso',
            self::Completado => 'Completado',
            self::Cancelado => 'Cancelado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Programado => 'warning',
            self::EnProceso => 'info',
            self::Completado => 'success',
            self::Cancelado => 'danger',
        };
    }
}
