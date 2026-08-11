<?php

declare(strict_types=1);

namespace App\Enums\Reservas;

enum OrigenReservaDetalle: int
{
    case RESERVA_INICIAL = 1;
    case RECEPCION = 2;
    case HUESPED = 3;
    case RESTAURANTE = 4;
    case HOUSEKEEPING = 5;
    case SPA = 6;
    case SISTEMA = 7;

    public function getLabel(): string
    {
        return match ($this) {
            self::RESERVA_INICIAL => 'Reserva Inicial',
            self::RECEPCION => 'Recepción',
            self::HUESPED => 'Huésped',
            self::RESTAURANTE => 'Restaurante',
            self::HOUSEKEEPING => 'Housekeeping / Limpieza',
            self::SPA => 'Spa / Actividades',
            self::SISTEMA => 'Proceso Automático',
        };
    }
}
