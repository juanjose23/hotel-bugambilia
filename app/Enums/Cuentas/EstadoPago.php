<?php

declare(strict_types=1);

namespace App\Enums\Cuentas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Estado de un pago/abono registrado contra una cuenta.
 * Migrado desde App\Enums\Estancias\EstadoPago.
 */
enum EstadoPago: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    /** Pago registrado pero no confirmado (ej: transferencia en espera de verificación) */
    case PENDIENTE = 1;

    /** Pago confirmado y aplicado al saldo de la cuenta */
    case APLICADO = 2;

    /** Pago anulado por error o corrección operativa */
    case ANULADO = 3;

    /** Monto devuelto al cliente por reembolso o cancelación */
    case REEMBOLSADO = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::APLICADO => 'Aplicado',
            self::ANULADO => 'Anulado',
            self::REEMBOLSADO => 'Reembolsado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::APLICADO => 'success',
            self::ANULADO => 'danger',
            self::REEMBOLSADO => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'heroicon-o-clock',
            self::APLICADO => 'heroicon-o-check-circle',
            self::ANULADO => 'heroicon-o-x-circle',
            self::REEMBOLSADO => 'heroicon-o-arrow-path',
        };
    }

    /** Indica si el pago tiene efecto real sobre el saldo de la cuenta */
    public function afectaSaldo(): bool
    {
        return $this === self::APLICADO;
    }
}
