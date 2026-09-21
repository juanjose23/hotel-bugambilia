<?php

declare(strict_types=1);

namespace App\Enums\Reportes;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SeveridadAnomalia: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Baja = 1;
    case Media = 2;
    case Alta = 3;
    case Critica = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Baja => 'Baja',
            self::Media => 'Media',
            self::Alta => 'Alta',
            self::Critica => 'Crítica',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Baja => 'gray',
            self::Media => 'warning',
            self::Alta => 'danger',
            self::Critica => 'danger',
        };
    }
}
