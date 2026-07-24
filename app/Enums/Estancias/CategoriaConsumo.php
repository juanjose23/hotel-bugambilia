<?php

declare(strict_types=1);

namespace App\Enums\Estancias;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CategoriaConsumo: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case HOSPEDAJE = 'hospedaje';
    case RESTAURANTE = 'restaurante';
    case MINIBAR = 'minibar';
    case LAVANDERIA = 'lavanderia';
    case TRANSPORTE = 'transporte';
    case SERVICIO_HABITACION = 'servicio_habitacion';
    case DANOS = 'danos';
    case PENALIZACIONES = 'penalizaciones';
    case OTROS_SERVICIOS = 'otros_servicios';
    case DESCUENTO = 'descuento';
    case AJUSTE = 'ajuste';

    public function getLabel(): string
    {
        return match ($this) {
            self::HOSPEDAJE => 'Hospedaje',
            self::RESTAURANTE => 'Restaurante',
            self::MINIBAR => 'Minibar',
            self::LAVANDERIA => 'Lavandería',
            self::TRANSPORTE => 'Transporte',
            self::SERVICIO_HABITACION => 'Servicio a la habitación',
            self::DANOS => 'Daños',
            self::PENALIZACIONES => 'Penalizaciones',
            self::OTROS_SERVICIOS => 'Otros servicios',
            self::DESCUENTO => 'Descuento',
            self::AJUSTE => 'Ajuste',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HOSPEDAJE => 'info',
            self::RESTAURANTE => 'success',
            self::MINIBAR => 'warning',
            self::LAVANDERIA => 'gray',
            self::TRANSPORTE => 'primary',
            self::SERVICIO_HABITACION => 'success',
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
            self::HOSPEDAJE => 'heroicon-o-home',
            self::RESTAURANTE => 'heroicon-o-cake',
            self::MINIBAR => 'heroicon-o-beaker',
            self::LAVANDERIA => 'heroicon-o-sparkles',
            self::TRANSPORTE => 'heroicon-o-truck',
            self::SERVICIO_HABITACION => 'heroicon-o-bell',
            self::DANOS => 'heroicon-o-exclamation-triangle',
            self::PENALIZACIONES => 'heroicon-o-x-circle',
            self::OTROS_SERVICIOS => 'heroicon-o-cube',
            self::DESCUENTO => 'heroicon-o-tag',
            self::AJUSTE => 'heroicon-o-arrows-right-left',
        };
    }
}
