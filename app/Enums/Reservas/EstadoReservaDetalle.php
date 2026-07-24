<?php

declare(strict_types=1);

namespace App\Enums\Reservas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasLabel;

enum EstadoReservaDetalle: int implements HasLabel
{
    use TieneAyudantesEnum;

    case PENDIENTE = 1;
    case CONFIRMADO = 2;
    case EN_USO = 3;
    case COMPLETADO = 4;
    case CANCELADO = 5;
    case REPROGRAMADO = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::CONFIRMADO => 'Confirmado',
            self::EN_USO => 'En uso',
            self::COMPLETADO => 'Completado',
            self::CANCELADO => 'Cancelado',
            self::REPROGRAMADO => 'Reprogramado',
        };
    }
}
