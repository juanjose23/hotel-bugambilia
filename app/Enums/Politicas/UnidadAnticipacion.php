<?php

declare(strict_types=1);

namespace App\Enums\Politicas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UnidadAnticipacion: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case DIAS = 1;
    case HORAS = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::DIAS => 'Días',
            self::HORAS => 'Horas',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DIAS => 'info',
            self::HORAS => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::DIAS => 'heroicon-o-calendar',
            self::HORAS => 'heroicon-o-clock',
        };
    }
}
