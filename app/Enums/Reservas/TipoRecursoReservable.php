<?php

declare(strict_types=1);

namespace App\Enums\Reservas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasLabel;

enum TipoRecursoReservable: int implements HasLabel
{
    use TieneAyudantesEnum;

    case HABITACION = 1;
    case ESPACIO = 2;
    case SERVICIO = 3;
    case PAQUETE = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::HABITACION => 'Habitación',
            self::ESPACIO => 'Espacio',
            self::SERVICIO => 'Servicio',
            self::PAQUETE => 'Paquete',
        };
    }
}
