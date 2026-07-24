<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use DomainException;
use Illuminate\Support\Facades\DB;

final class EnviarPedidoACocina
{
    public function __construct(
        private readonly ConsumirIngredientesPedido $consumirIngredientes,
    ) {}

    public function ejecutar(Pedido $pedido): Pedido
    {
        if ($pedido->items->isEmpty()) {
            throw new DomainException('No se puede enviar un pedido a cocina sin platillos seleccionados.');
        }

        return DB::transaction(function () use ($pedido): Pedido {
            foreach ($pedido->items as $item) {
                if ($item->estado === EstadoItemPedido::PENDIENTE) {
                    $item->update([
                        'estado' => EstadoItemPedido::EN_PREPARACION,
                    ]);

                    // Descontar inventario de insumos e ingredientes del plato
                    $this->consumirIngredientes->ejecutar($item);
                }
            }

            $pedido->update([
                'estado' => EstadoPedido::EN_PREPARACION,
            ]);

            return $pedido->refresh();
        });
    }
}
