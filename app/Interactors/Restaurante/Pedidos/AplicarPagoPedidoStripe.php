<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Actions\Facturacion\StripeMontoMenorUnidad;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Facturacion\ConfirmarPagoPasarela;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Persistencia\Facturacion\PagoTransaccionPersistencia;
use App\Repository\Queries\Facturacion\ObtenerTransaccionPorReferenciaQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidoParaConfirmacionPagoQuery;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final readonly class AplicarPagoPedidoStripe
{
    public function __construct(
        private ObtenerTransaccionPorReferenciaQuery $transacciones,
        private ObtenerPedidoParaConfirmacionPagoQuery $pedidos,
        private ConfirmarPagoPasarela $confirmarPago,
        private PagoTransaccionPersistencia $persistencia,
        private EnviarPedidoACocina $enviarCocina,
        private StripeMontoMenorUnidad $montoMenor,
    ) {}

    /**
     * @param  array<array-key, mixed>  $intent
     * @param  array<string, mixed>  $webhook
     */
    public function ejecutar(int $pedidoId, string $referencia, array $intent, array $webhook = []): PagoTransaccion
    {
        $confirmada = DB::transaction(function () use ($pedidoId, $referencia, $intent, $webhook): PagoTransaccion {
            $pedido = $this->pedidos->ejecutar($pedidoId);
            $transaccion = $this->transacciones->ejecutar($referencia);
            $metadata = is_array($intent['metadata'] ?? null) ? $intent['metadata'] : [];
            if (! $transaccion instanceof PagoTransaccion
                || (is_numeric($transaccion->request_payload['pedido_id'] ?? null) ? (int) $transaccion->request_payload['pedido_id'] : 0) !== $pedidoId
                || (is_numeric($metadata['pedido_id'] ?? null) ? (int) $metadata['pedido_id'] : 0) !== $pedidoId
                || ($intent['id'] ?? null) !== $referencia
                || ($intent['status'] ?? null) !== 'succeeded'
                || $pedido->estado === EstadoPedido::CANCELADO) {
                throw new DomainException('El pago no corresponde a este pedido o no está aprobado.');
            }
            $transaccion->loadMissing('moneda');
            $importe = $intent['amount_received'] ?? $intent['amount'] ?? null;
            if ($transaccion->moneda === null || $pedido->cuenta_id === null
                || (int) $transaccion->cuenta_id !== $pedido->cuenta_id
                || strtolower(is_string($intent['currency'] ?? null) ? $intent['currency'] : '') !== strtolower($transaccion->moneda->codigo)
                || (is_numeric($importe) ? (int) $importe : -1) !== $this->montoMenor->ejecutar((float) $transaccion->monto, $transaccion->moneda->codigo)) {
                throw new DomainException('El importe, moneda o cuenta del pago no coincide con el pedido.');
            }
            $confirmada = $this->confirmarPago->ejecutar($transaccion, $referencia, $webhook !== [] ? $webhook : $intent);
            $this->persistencia->actualizar($confirmada, ['response_payload' => $intent]);

            return $confirmada->refresh();
        });
        $pedido = $this->pedidos->ejecutar($pedidoId);
        if ($pedido->estado === EstadoPedido::ABIERTO) {
            try {
                $this->enviarCocina->ejecutar($pedido);
            } catch (DomainException $exception) {
                Log::warning('Pedido pagado pendiente de revisión en cocina.', [
                    'pedido_id' => $pedidoId, 'transaccion_id' => $confirmada->id, 'motivo' => $exception->getMessage(),
                ]);
            }
        }

        return $confirmada;
    }
}
