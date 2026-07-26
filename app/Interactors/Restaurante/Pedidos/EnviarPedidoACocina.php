<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Events\Restaurante\PedidoEnviadoACocina;
use App\Interactors\Restaurante\Cocina\ConsumirIngredientesPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class EnviarPedidoACocina
{
    public function __construct(
        private readonly ConsumirIngredientesPedido $consumirIngredientes,
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(Pedido $pedido): Pedido
    {
        $pedido->loadMissing(['items.plato.receta']);

        if ($pedido->items->isEmpty()) {
            throw new DomainException('No se puede enviar un pedido a cocina sin platillos seleccionados.');
        }

        return DB::transaction(function () use ($pedido): Pedido {
            $procesados = [];

            foreach ($pedido->items as $item) {
                if ($item->estado === EstadoItemPedido::PENDIENTE) {
                    $item->estado = EstadoItemPedido::EN_PREPARACION;
                    $this->repositorio->guardarItem($item);

                    $this->consumirIngredientes->ejecutar($item);
                    $procesados[] = $item->id;
                }
            }

            if ($procesados === []) {
                return $pedido;
            }

            $pedido->estado = EstadoPedido::EN_PREPARACION;
            $this->repositorio->guardarPedido($pedido);

            event(new PedidoEnviadoACocina($pedido, $procesados));

            return $pedido->refresh();
        });
    }
}
