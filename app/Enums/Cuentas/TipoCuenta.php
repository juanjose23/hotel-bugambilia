<?php

declare(strict_types=1);

namespace App\Enums\Cuentas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TipoCuenta: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case ESTANCIA = 1;
    case RESTAURANTE_DIRECTO = 2;
    case SERVICIO = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::ESTANCIA => 'Estancia / Habitación',
            self::RESTAURANTE_DIRECTO => 'Restaurante Directo POS',
            self::SERVICIO => 'Venta por Servicio',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ESTANCIA => 'info',
            self::RESTAURANTE_DIRECTO => 'warning',
            self::SERVICIO => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ESTANCIA => 'heroicon-o-home-modern',
            self::RESTAURANTE_DIRECTO => 'heroicon-o-shopping-bag',
            self::SERVICIO => 'heroicon-o-check-badge',
        };
    }
}
