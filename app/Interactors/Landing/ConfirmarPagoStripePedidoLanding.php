<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Actions\Restaurante\ConstruirUrlWhatsapp;
use App\BusinessLogic\Restaurante\Delivery\CalcularCostoYDetalleDelivery;
use App\Enums\Facturacion\EstadoIntentoStripe;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Queries\Facturacion\ObtenerTransaccionPorReferenciaQuery;
use App\Repository\Queries\Restaurante\Landing\ObtenerPedidoParaConfirmacionPagoQuery;
use App\WebServices\Stripe\StripePaymentIntentClient;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ConfirmarPagoStripePedidoLanding
{
    public function __construct(
        private StripePaymentIntentClient $stripeClient,
        private ObtenerPedidoParaConfirmacionPagoQuery $obtenerPedido,
        private ObtenerTransaccionPorReferenciaQuery $obtenerTransaccion,
        private CalcularCostoYDetalleDelivery $calcularCostoEntrega,
        private ConstruirUrlWhatsapp $construirUrlWhatsapp,
    ) {}

    /**
     * @return array{
     *     success: bool,
     *     pedido_id: int,
     *     codigo: string,
     *     estado: int,
     *     whatsapp_url: string,
     *     mensaje: string
     * }
     */
    public function ejecutar(int $pedidoId, string $paymentIntentId): array
    {
        return DB::transaction(function () use ($pedidoId, $paymentIntentId): array {
            $pedido = $this->obtenerPedido->ejecutar($pedidoId);

            // Verificar intento en Stripe
            $intent = $this->stripeClient->obtenerPaymentIntent($paymentIntentId);
            $status = is_string($intent['status'] ?? null) ? $intent['status'] : '';

            if ($status !== EstadoIntentoStripe::Exitoso->value) {
                throw new DomainException("El intento de pago no se encuentra aprobado en Stripe (estado: {$status}).");
            }

            // Actualizar transacción
            $transaccion = $this->obtenerTransaccion->ejecutar($paymentIntentId);

            if ($transaccion instanceof PagoTransaccion) {
                $transaccion->update([
                    'estado' => EstadoTransaccionPago::Capturada,
                    'response_payload' => $intent,
                ]);
            }

            // Actualizar notas del pedido indicando pago confirmado
            $pedido->update([
                'notas' => "✅ PAGO STRIPE CONFIRMADO ({$paymentIntentId}) | ".$pedido->notas,
            ]);

            // Construir mensaje de WhatsApp con pago verificado
            $nombreCliente = $pedido->cliente?->persona->nombre_completo ?? 'Cliente';
            $costoEnvio = $this->calcularCostoEntrega->ejecutar(is_string($pedido->notas) ? $pedido->notas : null)['costo_envio'];

            $lineas = [
                '*¡Pedido Pagado con Tarjeta (Stripe) — Hotel Bugambilias!* 💳✅',
                "🔖 *Comanda Nº:* {$pedido->codigo}",
                '💰 *Total Pagado:* C$ '.number_format((float) $pedido->subtotal + $costoEnvio, 2),
                "👤 *Cliente:* {$nombreCliente}",
                "🧾 *Referencia de Pago:* {$paymentIntentId}",
                '',
                'El pago ha sido procesado exitosamente. ¿Me confirman el tiempo de entrega, por favor?',
            ];

            $whatsappUrl = $this->construirUrlWhatsapp->ejecutar($lineas);

            return [
                'success' => true,
                'pedido_id' => (int) $pedido->id,
                'codigo' => (string) $pedido->codigo,
                'estado' => EstadoPedido::PAGADO->value,
                'whatsapp_url' => $whatsappUrl,
                'mensaje' => "¡Pago del pedido {$pedido->codigo} confirmado exitosamente!",
            ];
        });
    }
}
