<?php

declare(strict_types=1);

namespace App\Enums\Estancias;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TipoTitular: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case HABITACION = 'HABITACION';
    case CLIENTE = 'CLIENTE';
    case HUESPED = 'HUESPED';

    public function getLabel(): string
    {
        return match ($this) {
            self::HABITACION => 'Habitación',
            self::CLIENTE => 'Cliente',
            self::HUESPED => 'Huésped',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HABITACION => 'info',
            self::CLIENTE => 'success',
            self::HUESPED => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HABITACION => 'heroicon-o-building-office',
            self::CLIENTE => 'heroicon-o-user',
            self::HUESPED => 'heroicon-o-users',
        };
    }
}
