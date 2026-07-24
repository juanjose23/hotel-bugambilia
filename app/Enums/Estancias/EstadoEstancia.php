<?php

declare(strict_types=1);

namespace App\Enums\Estancias;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoEstancia: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case PROGRAMADA = 1;
    case ACTIVA = 2;
    case EXTENDIDA = 3;
    case FINALIZADA = 4;
    case CANCELADA = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::PROGRAMADA => 'Programada',
            self::ACTIVA => 'Activa',
            self::EXTENDIDA => 'Extendida',
            self::FINALIZADA => 'Finalizada',
            self::CANCELADA => 'Cancelada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PROGRAMADA => 'info',
            self::ACTIVA => 'success',
            self::EXTENDIDA => 'warning',
            self::FINALIZADA => 'gray',
            self::CANCELADA => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PROGRAMADA => 'heroicon-o-clock',
            self::ACTIVA => 'heroicon-o-key',
            self::EXTENDIDA => 'heroicon-o-arrow-long-right',
            self::FINALIZADA => 'heroicon-o-check-circle',
            self::CANCELADA => 'heroicon-o-x-circle',
        };
    }
}
