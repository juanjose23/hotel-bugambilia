<?php

declare(strict_types=1);

namespace App\Enums\Promociones;

enum TipoBeneficioCliente: string
{
    case DescuentoReserva = 'descuento_reserva';
    case DescuentoRestaurante = 'descuento_restaurante';
    case AnticipoReducido = 'anticipo_reducido';
    case LateCheckout = 'late_checkout';
    case UpgradeHabitacion = 'upgrade_habitacion';
    case Cortesia = 'cortesia';

    public function getLabel(): string
    {
        return match ($this) {
            self::DescuentoReserva => 'Descuento en reserva',
            self::DescuentoRestaurante => 'Descuento en restaurante',
            self::AnticipoReducido => 'Anticipo reducido',
            self::LateCheckout => 'Late checkout',
            self::UpgradeHabitacion => 'Upgrade de habitación',
            self::Cortesia => 'Cortesía',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DescuentoReserva, self::DescuentoRestaurante => 'success',
            self::AnticipoReducido => 'info',
            self::LateCheckout, self::UpgradeHabitacion => 'warning',
            self::Cortesia => 'primary',
        };
    }
}
