<?php

declare(strict_types=1);

namespace App\Enums\Estancias;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TipoMovimientoCuenta: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case CARGO = 'cargo';
    case PAGO = 'pago';
    case DESCUENTO = 'descuento';
    case AJUSTE = 'ajuste';

    public function getLabel(): string
    {
        return match ($this) {
            self::CARGO => 'Cargo',
            self::PAGO => 'Pago',
            self::DESCUENTO => 'Descuento',
            self::AJUSTE => 'Ajuste',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CARGO => 'danger',
            self::PAGO => 'success',
            self::DESCUENTO => 'warning',
            self::AJUSTE => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CARGO => 'heroicon-o-plus-circle',
            self::PAGO => 'heroicon-o-minus-circle',
            self::DESCUENTO => 'heroicon-o-tag',
            self::AJUSTE => 'heroicon-o-arrows-right-left',
        };
    }
}
