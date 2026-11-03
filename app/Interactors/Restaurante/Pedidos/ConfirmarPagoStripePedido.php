<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Actions\Restaurante\ConstruirUrlWhatsapp;
use App\BusinessLogic\Restaurante\Delivery\CalcularCostoYDetalleDelivery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidoParaConfirmacionPagoQuery;
use App\Support\Barcode\Support\MonedaHelper;
use App\WebServices\Stripe\StripePaymentIntentClient;

final readonly class ConfirmarPagoStripePedido
{
    public function __construct(
        private StripePaymentIntentClient $stripeClient,
        private ObtenerPedidoParaConfirmacionPagoQuery $obtenerPedido,
        private CalcularCostoYDetalleDelivery $calcularCostoEntrega,
        private ConstruirUrlWhatsapp $construirUrlWhatsapp,
        private AplicarPagoPedidoStripe $aplicarPago,
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
        $pedido = $this->obtenerPedido->ejecutar($pedidoId);

        // Verificar intento en Stripe
        $intent = $this->stripeClient->obtenerPaymentIntent($paymentIntentId);
        $this->aplicarPago->ejecutar($pedidoId, $paymentIntentId, $intent);
        $pedido->refresh();

        // Construir mensaje de WhatsApp con pago verificado
        $nombreCliente = $pedido->cliente?->persona->nombre_completo ?? 'Cliente';
        $costoEnvio = $this->calcularCostoEntrega->ejecutar(is_string($pedido->notas) ? $pedido->notas : null)['costo_envio'];

        $totalPagadoFmt = MonedaHelper::formatear((float) ($pedido->cuenta->total ?? ((float) $pedido->subtotal + $costoEnvio)), $pedido->cuenta?->moneda);
        $lineas = [
            '*¡Pedido Pagado con Tarjeta (Stripe) — Hotel Bugambilias!* 💳✅',
            "🔖 *Comanda Nº:* {$pedido->codigo}",
            "💰 *Total Pagado:* {$totalPagadoFmt}",
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
            'estado' => (int) $pedido->estado->value,
            'whatsapp_url' => $whatsappUrl,
            'mensaje' => implode("\n", $lineas),
        ];
    }
}
