<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\Enums\Cuentas\MetodoPago;
use App\Enums\Reservas\TipoPagoReserva;
use App\Interactors\Facturacion\Stripe\CrearIntentoPagoStripeReserva;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CrearReservaPublica
{
    public function __construct(
        private CrearReserva $crearReserva,
        private CrearIntentoPagoStripeReserva $crearIntentoStripe,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $servicios
     * @param  array<int, mixed>  $espacios
     * @return array{reserva: Reserva, requiere_pago_stripe: bool, stripe_pago: ?array{client_secret: string, publishable_key: string, transaccion: PagoTransaccion, monto: float, moneda: string}}
     *
     * @throws Throwable
     */
    public function ejecutar(array $datos, array $servicios, array $espacios, ?int $clienteId): array
    {
        return DB::transaction(function () use ($datos, $servicios, $espacios, $clienteId): array {
            $datos['cliente_id'] = $clienteId;
            $datos['origen_pago_reserva'] = 'publico';
            $datos = $this->normalizarPagoPublico($datos);

            $reserva = $this->crearReserva->ejecutar($datos, $servicios, $espacios);

            $requierePagoStripe = ($datos['canal_pago_reserva'] ?? 'stripe') === 'stripe'
                && $reserva->tipo_pago !== TipoPagoReserva::SIN_PAGO;

            return [
                'reserva' => $reserva,
                'requiere_pago_stripe' => $requierePagoStripe,
                'stripe_pago' => $requierePagoStripe
                    ? $this->crearIntentoStripe->ejecutar($reserva)
                    : null,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function normalizarPagoPublico(array $datos): array
    {
        $tipoPago = $datos['tipo_pago_reserva'] ?? null;
        $canalPago = $datos['canal_pago_reserva'] ?? null;
        $metodoPago = $datos['metodo_pago_reserva'] ?? $datos['metodo_pago_abono'] ?? null;

        if (! is_string($tipoPago) || TipoPagoReserva::tryFrom($tipoPago) === null) {
            unset($datos['tipo_pago_reserva']);
        }

        if ($canalPago === null && $metodoPago === null) {
            $datos['canal_pago_reserva'] = 'stripe';
        }

        if (($datos['canal_pago_reserva'] ?? null) === 'transferencia') {
            $datos['metodo_pago_reserva'] = MetodoPago::TRANSFERENCIA->value;
        }

        return $datos;
    }
}
