<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoIndividualizacion: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Pendiente = 1;
    case EnProceso = 2;
    case Completado = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnProceso => 'En Proceso',
            self::Completado => 'Completado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::EnProceso => 'info',
            self::Completado => 'success',
        };
    }
}
