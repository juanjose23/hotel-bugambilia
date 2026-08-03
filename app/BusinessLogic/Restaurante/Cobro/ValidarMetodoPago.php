<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Cobro;

use App\Enums\Cuentas\MetodoPago;

final class ValidarMetodoPago
{
    private const METODOS_SIN_MONTO = [
        MetodoPago::CORTESIA,
        MetodoPago::CREDITO_CORPORATIVO,
        MetodoPago::PUNTOS_LEALTAD,
        MetodoPago::CARGO_HABITACION,
    ];

    /**
     * Valida que el monto recibido sea válido para el método de pago seleccionado.
     *
     * @throws \DomainException si el método o monto son inválidos
     */
    public function validar(MetodoPago $metodo, float $montoRecibido): void
    {
        if (in_array($metodo, self::METODOS_SIN_MONTO, strict: true)) {
            return;
        }

        if ($montoRecibido <= 0) {
            throw new \DomainException('El monto recibido debe ser mayor a cero para el método seleccionado.');
        }
    }

    public function esEfectivo(MetodoPago $metodo): bool
    {
        return $metodo === MetodoPago::EFECTIVO;
    }

    public function requiereReferencia(MetodoPago $metodo): bool
    {
        return in_array($metodo, [
            MetodoPago::TARJETA_CREDITO,
            MetodoPago::TARJETA_DEBITO,
            MetodoPago::TRANSFERENCIA,
            MetodoPago::DEPOSITO,
            MetodoPago::PAGO_QR,
        ], strict: true);
    }

    public function esCortesia(MetodoPago $metodo): bool
    {
        return $metodo === MetodoPago::CORTESIA;
    }

    public function esCreditoCorporativo(MetodoPago $metodo): bool
    {
        return $metodo === MetodoPago::CREDITO_CORPORATIVO;
    }

    public function esPuntosLealtad(MetodoPago $metodo): bool
    {
        return $metodo === MetodoPago::PUNTOS_LEALTAD;
    }

    public function esCargoHabitacion(MetodoPago $metodo): bool
    {
        return $metodo === MetodoPago::CARGO_HABITACION;
    }
}
