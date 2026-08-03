<?php

declare(strict_types=1);

namespace App\Enums\Reservas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasLabel;

enum TipoPagoReserva: string implements HasLabel
{
    use TieneAyudantesEnum;

    case SIN_PAGO = 'sin_pago';
    case ABONO_50 = 'abono_50';
    case PAGO_COMPLETO = 'pago_completo';

    public function getLabel(): string
    {
        return match ($this) {
            self::SIN_PAGO => 'Registrar sin pago',
            self::ABONO_50 => 'Abonar el 50 %',
            self::PAGO_COMPLETO => 'Pagar el total completo',
        };
    }

    public function monto(float $total): float
    {
        return match ($this) {
            self::SIN_PAGO => 0.0,
            self::ABONO_50 => round($total * 0.50, 2),
            self::PAGO_COMPLETO => round($total, 2),
        };
    }
}
