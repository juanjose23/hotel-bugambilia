<?php

declare(strict_types=1);

namespace App\Enums\Cuentas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Categoría del consumo/cargo registrado en un CuentaDetalle.
 * Migrado desde App\Enums\Estancias\CategoriaConsumo.
 * Aplica a cualquier tipo de cargo (Estancia, Restaurante, Servicios).
 */
enum CategoriaConsumo: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case HOSPEDAJE = 1;
    case RESTAURANTE = 2;
    case BAR = 3;
    case MINIBAR = 4;
    case LAVANDERIA = 5;
    case TRANSPORTE = 6;

    /** Servicio solicitado directamente a la habitación */
    case SERVICIO_HABITACION = 7;

    case SPA = 8;
    case DANOS = 9;
    case PENALIZACIONES = 10;
    case OTROS_SERVICIOS = 11;

    /** Reducción aplicada sobre el total de la cuenta */
    case DESCUENTO = 12;

    /** Ajuste contable (corrección de cargo, diferencia de redondeo, etc.) */
    case AJUSTE = 13;

    public function getLabel(): string
    {
        return match ($this) {
            self::HOSPEDAJE => 'Hospedaje',
            self::RESTAURANTE => 'Restaurante',
            self::BAR => 'Bar',
            self::MINIBAR => 'Minibar',
            self::LAVANDERIA => 'Lavandería',
            self::TRANSPORTE => 'Transporte',
            self::SERVICIO_HABITACION => 'Servicio a la Habitación',
            self::SPA => 'Spa',
            self::DANOS => 'Daños',
            self::PENALIZACIONES => 'Penalizaciones',
            self::OTROS_SERVICIOS => 'Otros Servicios',
            self::DESCUENTO => 'Descuento',
            self::AJUSTE => 'Ajuste',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HOSPEDAJE => 'info',
            self::RESTAURANTE => 'success',
            self::BAR => 'warning',
            self::MINIBAR => 'warning',
            self::LAVANDERIA => 'gray',
            self::TRANSPORTE => 'primary',
            self::SERVICIO_HABITACION => 'success',
            self::SPA => 'purple',
            self::DANOS => 'danger',
            self::PENALIZACIONES => 'danger',
            self::OTROS_SERVICIOS => 'gray',
            self::DESCUENTO => 'warning',
            self::AJUSTE => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HOSPEDAJE => 'heroicon-o-home-modern',
            self::RESTAURANTE => 'heroicon-o-shopping-bag',
            self::BAR => 'heroicon-o-beaker',
            self::MINIBAR => 'heroicon-o-archive-box',
            self::LAVANDERIA => 'heroicon-o-check-badge',
            self::TRANSPORTE => 'heroicon-o-truck',
            self::SERVICIO_HABITACION => 'heroicon-o-bell',
            self::SPA => 'heroicon-o-heart',
            self::DANOS => 'heroicon-o-exclamation-triangle',
            self::PENALIZACIONES => 'heroicon-o-x-circle',
            self::OTROS_SERVICIOS => 'heroicon-o-cube',
            self::DESCUENTO => 'heroicon-o-tag',
            self::AJUSTE => 'heroicon-o-arrows-right-left',
        };
    }

    /** Indica si el renglón suma al total de la cuenta (true) o lo reduce (false) */
    public function esCargo(): bool
    {
        return ! in_array($this, [self::DESCUENTO], strict: true);
    }
}
