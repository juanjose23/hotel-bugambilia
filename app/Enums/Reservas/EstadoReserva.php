<?php

declare(strict_types=1);

namespace App\Enums\Reservas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoReserva: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case PENDIENTE = 'pendiente';
    case CONFIRMADA = 'confirmada';
    case CHECKED_IN = 'checked_in';
    case CHECKED_OUT = 'checked_out';
    case CANCELADA = 'cancelada';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::CONFIRMADA => 'Confirmada',
            self::CHECKED_IN => 'Checked In',
            self::CHECKED_OUT => 'Checked Out',
            self::CANCELADA => 'Cancelada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::CONFIRMADA => 'info',
            self::CHECKED_IN => 'success',
            self::CHECKED_OUT => 'gray',
            self::CANCELADA => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'heroicon-o-clock',
            self::CONFIRMADA => 'heroicon-o-check-circle',
            self::CHECKED_IN => 'heroicon-o-key',
            self::CHECKED_OUT => 'heroicon-o-arrow-right-on-rectangle',
            self::CANCELADA => 'heroicon-o-x-circle',
        };
    }
}
