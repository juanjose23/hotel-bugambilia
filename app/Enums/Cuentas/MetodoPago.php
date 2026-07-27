<?php

declare(strict_types=1);

namespace App\Enums\Cuentas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Formas de pago aceptadas en el hotel.
 * Unifica App\Enums\Estancias\MetodoPago y App\Enums\Restaurante\MetodoPago.
 * Aplica a: Restaurante POS, Recepción, Servicios.
 */
enum MetodoPago: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case EFECTIVO = 1;
    case TARJETA_CREDITO = 2;
    case TARJETA_DEBITO = 3;
    case TRANSFERENCIA = 4;
    case DEPOSITO = 5;
    case PAGO_QR = 6;

    /** Consumo cargado a la cuenta de la estancia del huésped */
    case CARGO_HABITACION = 7;

    /** Cortesía o invitación sin cobro al cliente */
    case CORTESIA = 8;

    case CREDITO_CORPORATIVO = 9;
    case PUNTOS_LEALTAD = 10;

    public function getLabel(): string
    {
        return match ($this) {
            self::EFECTIVO => 'Efectivo',
            self::TARJETA_CREDITO => 'Tarjeta de Crédito',
            self::TARJETA_DEBITO => 'Tarjeta de Débito',
            self::TRANSFERENCIA => 'Transferencia Bancaria',
            self::DEPOSITO => 'Depósito Bancario',
            self::PAGO_QR => 'Pago QR',
            self::CARGO_HABITACION => 'Cargo a Habitación',
            self::CORTESIA => 'Cortesía',
            self::CREDITO_CORPORATIVO => 'Crédito Corporativo',
            self::PUNTOS_LEALTAD => 'Puntos / Lealtad',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EFECTIVO => 'success',
            self::TARJETA_CREDITO, self::TARJETA_DEBITO, self::CREDITO_CORPORATIVO => 'info',
            self::TRANSFERENCIA, self::DEPOSITO => 'primary',
            self::PAGO_QR, self::PUNTOS_LEALTAD => 'warning',
            self::CARGO_HABITACION => 'purple',
            self::CORTESIA => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::EFECTIVO => 'heroicon-o-banknotes',
            self::TARJETA_CREDITO, self::TARJETA_DEBITO => 'heroicon-o-credit-card',
            self::TRANSFERENCIA => 'heroicon-o-building-library',
            self::DEPOSITO => 'heroicon-o-document-check',
            self::PAGO_QR => 'heroicon-o-qr-code',
            self::CARGO_HABITACION => 'heroicon-o-home-modern',
            self::CORTESIA => 'heroicon-o-gift',
            self::CREDITO_CORPORATIVO => 'heroicon-o-briefcase',
            self::PUNTOS_LEALTAD => 'heroicon-o-star',
        };
    }

    /** Determina si el método genera movimiento de efectivo real en caja */
    public function esEfectivoReal(): bool
    {
        return in_array($this, [self::EFECTIVO, self::TARJETA_CREDITO, self::TARJETA_DEBITO], strict: true);
    }
}
