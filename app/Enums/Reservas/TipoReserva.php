<?php

declare(strict_types=1);

namespace App\Enums\Reservas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TipoReserva: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case HABITACION = 'habitacion';
    case RESTAURANTE = 'restaurante';
    case SERVICIO = 'servicio';
    case PAQUETE = 'paquete';

    public function getLabel(): string
    {
        return match ($this) {
            self::HABITACION => 'Habitación',
            self::RESTAURANTE => 'Restaurante / Ambiente',
            self::SERVICIO => 'Servicio',
            self::PAQUETE => 'Paquete Completo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HABITACION => 'primary',
            self::RESTAURANTE => 'warning',
            self::SERVICIO => 'info',
            self::PAQUETE => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HABITACION => 'heroicon-o-home',
            self::RESTAURANTE => 'heroicon-o-building-storefront',
            self::SERVICIO => 'heroicon-o-check-badge',
            self::PAQUETE => 'heroicon-o-gift',
        };
    }
}
