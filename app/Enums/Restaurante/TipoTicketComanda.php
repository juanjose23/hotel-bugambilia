<?php

declare(strict_types=1);

namespace App\Enums\Restaurante;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipoTicketComanda: string implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case NUEVO = 'nuevo';
    case AGREGADO = 'agregado';
    case CANCELACION = 'cancelacion';
    case REIMPRESION = 'reimpresion';

    public function getLabel(): string
    {
        return match ($this) {
            self::NUEVO => 'NUEVO PEDIDO',
            self::AGREGADO => 'ADICIÓN / AGREGADO',
            self::CANCELACION => 'CANCELACIÓN DE PLATILLO',
            self::REIMPRESION => 'REIMPRESIÓN DE COMANDA',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NUEVO => 'success',
            self::AGREGADO => 'info',
            self::CANCELACION => 'danger',
            self::REIMPRESION => 'warning',
        };
    }
}
