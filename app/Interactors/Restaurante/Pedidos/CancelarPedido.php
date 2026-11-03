<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\BusinessLogic\Restaurante\Validaciones\ValidarTransicionPedido;
use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Cuentas\Gestion\RecalcularCuenta;
use App\Interactors\Restaurante\Cocina\AnularItemPedido;
use App\Interactors\Restaurante\Mesas\CambiarEstadoMesa;
use App\Notifications\Restaurante\NotificadorRestaurante;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerCargoEnvioPedidoQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerPedidoParaConfirmacionPagoQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerSeguimientoPedidoQuery;
use App\WebServices\Stripe\StripePaymentIntentClient;
use Illuminate\Support\Facades\DB;

final readonly class CancelarPedido
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private NotificadorRestaurante $notificador,
        private RecalcularTotalesPedido $recalcular,
        private CambiarEstadoMesa $cambiarEstadoMesa,
        private ValidarTransicionPedido $validarTransicion,
        private AnularItemPedido $anularItem,
        private ObtenerPedidoParaConfirmacionPagoQuery $pedidosBloqueados,
        private ObtenerCargoEnvioPedidoQuery $cargoEnvio,
        private ObtenerSeguimientoPedidoQuery $seguimiento,
        private CuentaRepositorioInterface $cuentas,
        private RecalcularCuenta $recalcularCuenta,
        private StripePaymentIntentClient $stripe,

    ) {}

    public function ejecutar(Pedido $pedido): Pedido
    {
        return DB::transaction(function () use ($pedido): Pedido {
            $pedido = $this->pedidosBloqueados->ejecutar($pedido->id);
            $this->validarTransicion->puedeCancelar($pedido);
            $pedido->load('items');
            $cuenta = $pedido->cuenta;
            if ($cuenta !== null && (float) $cuenta->total_pagado > 0) {
                throw new \DomainException('El pedido tiene pagos. Tramita el ajuste o reembolso autorizado antes de cancelarlo.');
            }
            $transaccion = $this->seguimiento->transaccion($pedido->id);
            if ($transaccion !== null && in_array($transaccion->estado, [EstadoTransaccionPago::Pendiente, EstadoTransaccionPago::Fallida], true)
                && is_string($transaccion->referencia_pasarela) && $transaccion->referencia_pasarela !== '') {
                $this->stripe->cancelarPaymentIntent($transaccion->referencia_pasarela);
            }

            foreach ($pedido->items as $item) {
                if ($item->estado !== EstadoItemPedido::ANULADO && $item->estado !== EstadoItemPedido::SERVIDO) {
                    $this->anularItem->ejecutar($item->id);
                }
            }

            $envio = $this->cargoEnvio->ejecutar($pedido);
            if ($envio !== null && ! $pedido->items->contains(fn ($item): bool => $item->estado === EstadoItemPedido::SERVIDO)) {
                $this->cuentas->actualizarDetalle($envio, ['estado' => EstadoGeneral::Inactivo->value, 'anulado_en' => now()]);
            }
            if ($cuenta !== null) {
                $this->recalcularCuenta->ejecutar($cuenta);
            }
            $pedido->estado = EstadoPedido::CANCELADO;
            $pedido->cerrado_en = now();
            $this->repositorio->guardarPedido($pedido);

            $mesa = $pedido->mesa;
            if ($mesa) {
                $this->cambiarEstadoMesa->ejecutar($mesa->id, EstadoEspacio::Sucio, MotivoTransicionMesa::CierrePedido);
            }

            $this->recalcular->ejecutar($pedido);
            $this->notificador->pedidoCancelado($pedido);

            return $pedido;
        });
    }
}
