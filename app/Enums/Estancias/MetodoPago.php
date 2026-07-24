<?php

declare(strict_types=1);

namespace App\Enums\Estancias;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MetodoPago: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case EFECTIVO = 'efectivo';
    case TARJETA_CREDITO = 'tarjeta_credito';
    case TARJETA_DEBITO = 'tarjeta_debito';
    case TRANSFERENCIA = 'transferencia';
    case DEPOSITO = 'deposito';
    case PAGO_QR = 'pago_qr';
    case PASARELA_ONLINE = 'pasarela_online';
    case CREDITO_CORPORATIVO = 'credito_corporativo';
    case PUNTOS_LEALTAD = 'puntos_lealtad';

    public function getLabel(): string
    {
        return match ($this) {
            self::EFECTIVO => 'Efectivo',
            self::TARJETA_CREDITO => 'Tarjeta de crédito',
            self::TARJETA_DEBITO => 'Tarjeta de débito',
            self::TRANSFERENCIA => 'Transferencia bancaria',
            self::DEPOSITO => 'Depósito bancario',
            self::PAGO_QR => 'Pago QR',
            self::PASARELA_ONLINE => 'Pasarela online',
            self::CREDITO_CORPORATIVO => 'Crédito corporativo',
            self::PUNTOS_LEALTAD => 'Puntos / Lealtad',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EFECTIVO => 'success',
            self::TARJETA_CREDITO => 'info',
            self::TARJETA_DEBITO => 'info',
            self::TRANSFERENCIA => 'primary',
            self::DEPOSITO => 'primary',
            self::PAGO_QR => 'warning',
            self::PASARELA_ONLINE => 'info',
            self::CREDITO_CORPORATIVO => 'purple',
            self::PUNTOS_LEALTAD => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::EFECTIVO => 'heroicon-o-banknotes',
            self::TARJETA_CREDITO => 'heroicon-o-credit-card',
            self::TARJETA_DEBITO => 'heroicon-o-credit-card',
            self::TRANSFERENCIA => 'heroicon-o-building-library',
            self::DEPOSITO => 'heroicon-o-document-check',
            self::PAGO_QR => 'heroicon-o-qr-code',
            self::PASARELA_ONLINE => 'heroicon-o-globe-alt',
            self::CREDITO_CORPORATIVO => 'heroicon-o-briefcase',
            self::PUNTOS_LEALTAD => 'heroicon-o-sparkles',
        };
    }
}
