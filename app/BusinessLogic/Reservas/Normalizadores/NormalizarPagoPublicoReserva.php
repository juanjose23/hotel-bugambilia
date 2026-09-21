<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Normalizadores;

use App\BusinessLogic\Reservas\Resolutores\ResolverTipoPagoReserva;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Reservas\TipoPagoReserva;

final class NormalizarPagoPublicoReserva
{
    public function __construct(
        private readonly ResolverTipoPagoReserva $resolverTipoPago,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function normalizar(array $datos): array
    {
        $tipoPago = $datos['tipo_pago_reserva'] ?? null;
        $canalPago = $datos['canal_pago_reserva'] ?? null;
        $metodoPago = $datos['metodo_pago_reserva'] ?? $datos['metodo_pago_abono'] ?? null;

        if (! is_string($tipoPago) || TipoPagoReserva::tryFrom($tipoPago) === null) {
            unset($datos['tipo_pago_reserva']);
        }

        if ($canalPago === 'transferencia') {
            $datos['metodo_pago_reserva'] = MetodoPago::TRANSFERENCIA->value;
            $datos['canal_pago_reserva'] = 'transferencia';
        } elseif ($canalPago === 'stripe' || (! is_numeric($metodoPago) && ($datos['tipo_pago_reserva'] ?? null) !== TipoPagoReserva::SIN_PAGO->value)) {
            $datos['canal_pago_reserva'] = 'stripe';
            unset($datos['metodo_pago_reserva'], $datos['metodo_pago_abono']);
        }

        return $datos;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function esPagoPorStripe(array $datos): bool
    {
        $tipoPago = $this->resolverTipoPago->resolver($datos);

        if (($datos['canal_pago_reserva'] ?? null) === 'stripe') {
            return true;
        }

        return ($datos['origen_pago_reserva'] ?? null) === 'publico'
            && $tipoPago !== TipoPagoReserva::SIN_PAGO
            && ! is_numeric($datos['metodo_pago_reserva'] ?? $datos['metodo_pago_abono'] ?? null);
    }
}
