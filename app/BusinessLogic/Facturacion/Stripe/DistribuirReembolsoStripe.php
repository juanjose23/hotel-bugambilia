<?php

declare(strict_types=1);

namespace App\BusinessLogic\Facturacion\Stripe;

use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Collection;

final class ItemDistribucionReembolso
{
    public function __construct(
        public readonly PagoTransaccion $transaccion,
        public readonly float $montoAReembolsar,
        public readonly string $paymentIntentId,
        public readonly string $monedaCodigo,
    ) {}
}

final class DistribuirReembolsoStripe
{
    /**
     * @param  Collection<int, PagoTransaccion>  $transacciones
     * @return ItemDistribucionReembolso[]
     */
    public function distribuir(Collection $transacciones, float $montoTotalReembolso, Reserva $reserva): array
    {
        $restante = round($montoTotalReembolso, 2);
        $distribucion = [];

        foreach ($transacciones as $transaccion) {
            if ($restante <= 0.0) {
                break;
            }

            $monedaCodigo = $transaccion->moneda !== null
                ? (string) $transaccion->moneda->codigo
                : ($reserva->moneda !== null ? (string) $reserva->moneda->codigo : 'USD');

            $montoTransaccion = (float) $transaccion->monto;
            $montoAReembolsar = min($restante, $montoTransaccion);
            $paymentIntentId = (string) $transaccion->referencia_pasarela;

            $distribucion[] = new ItemDistribucionReembolso(
                transaccion: $transaccion,
                montoAReembolsar: round($montoAReembolsar, 2),
                paymentIntentId: $paymentIntentId,
                monedaCodigo: $monedaCodigo,
            );

            $restante = round($restante - $montoAReembolsar, 2);
        }

        return $distribucion;
    }
}
