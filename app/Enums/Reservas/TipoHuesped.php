<?php

declare(strict_types=1);

namespace App\Enums\Reservas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasLabel;

enum TipoHuesped: int implements HasLabel
{
    use TieneAyudantesEnum;

    case ADULTO = 1;
    case NINO = 2;
    case INFANTE = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::ADULTO => 'Adulto',
            self::NINO => 'Niño',
            self::INFANTE => 'Infante',
        };
    }
}
