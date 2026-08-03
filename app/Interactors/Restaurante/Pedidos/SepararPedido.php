<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class SepararPedido
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
        private RecalcularTotalesPedido $recalcular,
    ) {}

    /**
     * @param  array<int, int>  $itemIds
     *
     * @throws Throwable
     */
    public function ejecutar(
        Pedido $pedidoOriginal,
        array $itemIds,
    ): Pedido {
        $estadosTerminales = [
            EstadoPedido::PAGADO,
            EstadoPedido::CARGADO_A_HABITACION,
            EstadoPedido::CANCELADO,
        ];

        if (in_array($pedidoOriginal->estado, $estadosTerminales, true)) {
            throw new DomainException('No se puede dividir un pedido en estado terminal.');
        }

        if ($itemIds === []) {
            throw new DomainException('Debe seleccionar al menos un ítem para mover.');
        }

        $itemsAMover = $this->repositorio->obtenerItemsMoviblesDePedido($pedidoOriginal, $itemIds);

        if ($itemsAMover->isEmpty()) {
            throw new DomainException('Ninguno de los ítems seleccionados puede moverse.');
        }

        $totalNoAnulados = $this->repositorio->contarItemsNoAnuladosDePedido($pedidoOriginal);

        if ($totalNoAnulados - $itemsAMover->count() <= 0) {
            throw new DomainException('Debe dejar al menos un ítem en el pedido original.');
        }

        $codigo = 'PED-'.date('Ymd').'-'.strtoupper(Str::random(6));

        return DB::transaction(function () use ($pedidoOriginal, $itemsAMover, $codigo): Pedido {
            $estadoNuevo = EstadoPedido::ABIERTO;
            $nuevoPedido = new Pedido([
                'codigo' => $codigo,
                'mesa_id' => $pedidoOriginal->mesa_id,
                'mesero_id' => $pedidoOriginal->mesero_id,
                'cliente_id' => $pedidoOriginal->cliente_id,
                'estado' => $estadoNuevo,
                'subtotal' => 0.00,
                'padre_pedido_id' => $pedidoOriginal->id,
                'consecutivo_comanda' => 1,
                'abierto_en' => now(),
                'notas' => "Dividido de $pedidoOriginal->codigo",
            ]);

            $this->repositorio->guardarPedido($nuevoPedido);

            foreach ($itemsAMover as $item) {
                $this->repositorio->guardarItem(new PedidoItem([
                    'pedido_id' => $nuevoPedido->id,
                    'plato_id' => $item->plato_id,
                    'area_cocina' => $item->area_cocina,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->subtotal,
                    'estado' => EstadoItemPedido::PENDIENTE,
                    'notas' => $item->notas,
                    'observaciones' => $item->observaciones,
                ]));

                $this->repositorio->eliminarItem($item);
            }

            $this->recalcular->ejecutar($pedidoOriginal);
            $this->recalcular->ejecutar($nuevoPedido);

            return $nuevoPedido;
        });
    }
}
