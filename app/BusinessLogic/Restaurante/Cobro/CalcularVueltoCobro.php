<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Cobro;

final class CalcularVueltoCobro
{
    /**
     * Calcula el vuelto (monto recibido - saldo pendiente).
     * Si el resultado es negativo, retorna 0.
     */
    public function calcular(float $montoRecibido, float $saldoPendiente): float
    {
        return max(0.0, round($montoRecibido - $saldoPendiente, 2));
    }

    /**
     * Calcula la propina implícita (diferencia entre monto recibido y saldo).
     * Solo aplica cuando el monto recibido es mayor al saldo pendiente.
     */
    public function calcularPropinaImplicita(float $montoRecibido, float $saldoPendiente): float
    {
        return max(0.0, $montoRecibido - $saldoPendiente);
    }
}
