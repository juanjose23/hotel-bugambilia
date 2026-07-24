<?php

declare(strict_types=1);

namespace App\Enums\Restaurante;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoPedido: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case ABIERTO = 'abierto';
    case EN_PREPARACION = 'en_preparacion';
    case LISTO = 'listo';
    case SERVIDO = 'servido';
    case PAGADO = 'pagado';
    case CARGADO_A_HABITACION = 'cargado_a_habitacion';
    case CANCELADO = 'cancelado';

    public function getLabel(): string
    {
        return match ($this) {
            self::ABIERTO => 'Abierto',
            self::EN_PREPARACION => 'En Preparación',
            self::LISTO => 'Listo',
            self::SERVIDO => 'Servido',
            self::PAGADO => 'Pagado',
            self::CARGADO_A_HABITACION => 'Cargado a Habitación',
            self::CANCELADO => 'Cancelado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ABIERTO => 'warning',
            self::EN_PREPARACION => 'info',
            self::LISTO => 'success',
            self::SERVIDO => 'primary',
            self::PAGADO => 'success',
            self::CARGADO_A_HABITACION => 'purple',
            self::CANCELADO => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ABIERTO => 'heroicon-o-folder-open',
            self::EN_PREPARACION => 'heroicon-o-fire',
            self::LISTO => 'heroicon-o-check',
            self::SERVIDO => 'heroicon-o-check-circle',
            self::PAGADO => 'heroicon-o-banknotes',
            self::CARGADO_A_HABITACION => 'heroicon-o-home',
            self::CANCELADO => 'heroicon-o-x-circle',
        };
    }
}
