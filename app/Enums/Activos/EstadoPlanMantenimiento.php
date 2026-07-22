<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoPlanMantenimiento: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Activo = 1;
    case Inactivo = 2;
    case Completado = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Inactivo => 'Inactivo',
            self::Completado => 'Completado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Activo => 'success',
            self::Inactivo => 'danger',
            self::Completado => 'info',
        };
    }
}
