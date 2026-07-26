<?php

declare(strict_types=1);

namespace App\Enums\Cuentas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Estado del ciclo de vida de una cuenta unificada del hotel.
 * Aplica a: Estancia, Restaurante Directo, Servicios.
 */
enum EstadoCuenta: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    /** Cuenta creada desde una reserva, pendiente de activación en Check-In */
    case SOLICITADA = 1;

    /** Cuenta activa y lista para recibir cargos y consumos */
    case ABIERTA = 2;

    /** Suspendida por exceder el límite de crédito autorizado */
    case BLOQUEADA = 3;

    /** Pre-cerrada: ticket/pre-factura emitida, esperando cobro */
    case PENDIENTE_PAGO = 4;

    /** Liquidada en su totalidad — saldo = $0.00 */
    case CERRADA = 5;

    /** Cancelada por devolución total o error administrativo */
    case ANULADA = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::SOLICITADA => 'Solicitada',
            self::ABIERTA => 'Abierta',
            self::BLOQUEADA => 'Bloqueada (Crédito)',
            self::PENDIENTE_PAGO => 'Pendiente de Pago',
            self::CERRADA => 'Cerrada',
            self::ANULADA => 'Anulada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SOLICITADA => 'info',
            self::ABIERTA => 'success',
            self::BLOQUEADA => 'danger',
            self::PENDIENTE_PAGO => 'warning',
            self::CERRADA => 'gray',
            self::ANULADA => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SOLICITADA => 'heroicon-o-document-text',
            self::ABIERTA => 'heroicon-o-folder-open',
            self::BLOQUEADA => 'heroicon-o-lock-closed',
            self::PENDIENTE_PAGO => 'heroicon-o-clock',
            self::CERRADA => 'heroicon-o-check-badge',
            self::ANULADA => 'heroicon-o-x-circle',
        };
    }

    /** Determina si la cuenta puede recibir nuevos cargos */
    public function permiteNuevosCargos(): bool
    {
        return $this === self::ABIERTA;
    }

    /** Determina si la cuenta puede ser cerrada definitivamente */
    public function puedeCerrarse(): bool
    {
        return in_array($this, [self::ABIERTA, self::PENDIENTE_PAGO], strict: true);
    }
}
