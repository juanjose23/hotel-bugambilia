<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\BusinessLogic\Restaurante\Validaciones\ValidarTransicionPedido;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Enums\Restaurante\MotivoTransicionMesa;
use App\Interactors\Restaurante\Mesas\CambiarEstadoMesa;
use App\Notifications\Restaurante\NotificadorRestaurante;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class CancelarPedido
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private NotificadorRestaurante $notificador,
        private RecalcularTotalesPedido $recalcular,
        private CambiarEstadoMesa $cambiarEstadoMesa,
        private ValidarTransicionPedido $validarTransicion,
    ) {}

    public function ejecutar(Pedido $pedido): Pedido
    {
        $this->validarTransicion->puedeCancelar($pedido);

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
