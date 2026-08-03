<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class ConfirmarPedidoKiosko
{
    public function __construct(
        private AbrirPedidoMesa $abrirPedido,
        private EnviarPedidoACocina $enviarCocina,
        private RestauranteRepositorioInterface $repositorio,
        private RecalcularTotalesPedido $recalcular,
    ) {}

    /**
     * @param  array<int, array{plato_id: int, precio: float, cantidad: int, observaciones: string}>  $items
     */
    public function ejecutar(
        array $items,
        ?Espacio $mesa = null,
        ?int $meseroId = null,
        ?string $notas = null,
    ): Pedido {
        return DB::transaction(function () use ($items, $mesa, $meseroId, $notas): Pedido {
            $pedido = $this->abrirPedido->ejecutar(
                mesa: $mesa,
                meseroId: $meseroId,
                notas: $notas ?? 'Auto-pedido generado en kiosko digital',
            );

            foreach ($items as $item) {
                $this->repositorio->crearPedidoItem([
                    'pedido_id' => $pedido->id,
                    'plato_id' => $item['plato_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => round($item['precio'] * $item['cantidad'], 2),
                    'observaciones' => $item['observaciones'] !== '' ? $item['observaciones'] : null,
                    'estado' => EstadoItemPedido::PENDIENTE,
                ]);
            }

            $pedido->loadMissing('items');
            $subtotal = $pedido->items->sum('subtotal');
            $pedido->subtotal = is_numeric($subtotal) ? (float) $subtotal : 0.0;
            $this->repositorio->guardarPedido($pedido);

            $this->recalcular->ejecutar($pedido);
            $this->enviarCocina->ejecutar($pedido);

            return $pedido;
        });
    }
}
