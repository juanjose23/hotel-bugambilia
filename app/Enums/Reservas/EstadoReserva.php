<?php

declare(strict_types=1);

namespace App\Enums\Reservas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoReserva: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case PENDIENTE = 1;
    case CONFIRMADA = 2;
    case PARCIALMENTE_CHECKED_IN = 3;
    case CHECKED_IN = 4;
    case PARCIALMENTE_CHECKED_OUT = 5;
    case CHECKED_OUT = 6;
    case CANCELADA = 7;
    case NO_SHOW = 8;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::CONFIRMADA => 'Confirmada',
            self::PARCIALMENTE_CHECKED_IN => 'Parcialmente Checked In',
            self::CHECKED_IN => 'Checked In',
            self::PARCIALMENTE_CHECKED_OUT => 'Parcialmente Checked Out',
            self::CHECKED_OUT => 'Checked Out',
            self::CANCELADA => 'Cancelada',
            self::NO_SHOW => 'No Show',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::CONFIRMADA => 'info',
            self::PARCIALMENTE_CHECKED_IN => 'primary',
            self::CHECKED_IN => 'success',
            self::PARCIALMENTE_CHECKED_OUT => 'warning',
            self::CHECKED_OUT => 'gray',
            self::CANCELADA => 'danger',
            self::NO_SHOW => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'heroicon-o-clock',
            self::CONFIRMADA => 'heroicon-o-check-circle',
            self::PARCIALMENTE_CHECKED_IN => 'heroicon-o-key',
            self::CHECKED_IN => 'heroicon-o-key',
            self::PARCIALMENTE_CHECKED_OUT => 'heroicon-o-arrow-right-end-on-rectangle',
            self::CHECKED_OUT => 'heroicon-o-arrow-right-end-on-rectangle',
            self::CANCELADA => 'heroicon-o-x-circle',
            self::NO_SHOW => 'heroicon-o-exclamation-triangle',
        };
    }
}
