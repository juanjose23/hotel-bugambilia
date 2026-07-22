<?php

declare(strict_types=1);

namespace App\Enums\Shared;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoEjecucionJob: string implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Pendiente = 'pendiente';
    case Ejecutando = 'ejecutando';
    case Completado = 'completado';
    case Fallido = 'fallido';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Ejecutando => 'Ejecutando',
            self::Completado => 'Completado',
            self::Fallido => 'Fallido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'gray',
            self::Ejecutando => 'warning',
            self::Completado => 'success',
            self::Fallido => 'danger',
        };
    }
}
