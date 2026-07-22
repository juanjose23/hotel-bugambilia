<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasLabel;

enum TipoPrecioEspacio: string implements HasLabel
{
    use TieneAyudantesEnum;

    case Base = 'base';
    case PorHora = 'por_hora';

    public function getLabel(): string
    {
        return match ($this) {
            self::Base => 'Precio Base',
            self::PorHora => 'Por Hora',
        };
    }
}
