<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Interactors\Restaurante\Mesas\CambiarEstadoMesa;
use App\Notifications\Restaurante\NotificadorRestaurante;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CancelarPedido
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
        private readonly NotificadorRestaurante $notificador,
        private readonly RecalcularTotalesPedido $recalcular,
        private readonly CambiarEstadoMesa $cambiarEstadoMesa,
    ) {}

    public function ejecutar(Pedido $pedido): Pedido
    {
        $estadosTerminales = [EstadoPedido::PAGADO, EstadoPedido::CARGADO_A_HABITACION, EstadoPedido::CANCELADO];

        if (in_array($pedido->estado, $estadosTerminales, true)) {
            throw new DomainException("El pedido #{$pedido->codigo} no puede ser cancelado (estado: {$pedido->estado->getLabel()}).");
        }

        return DB::transaction(function () use ($pedido): Pedido {
            $pedido->loadMissing('items');

            foreach ($pedido->items as $item) {
                if ($item->estado !== EstadoItemPedido::ANULADO && $item->estado !== EstadoItemPedido::SERVIDO) {
                    $item->estado = EstadoItemPedido::ANULADO;
                    $this->repositorio->guardarItem($item);
                }
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
