<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Cocina\AnalizarFaltantesPedidoCocina;
use DomainException;
use Illuminate\Support\Collection;

final readonly class BloquearItemsPorFaltaStock
{
    public function __construct(
        private AnalizarFaltantesPedidoCocina $analizarFaltantes,
        private RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function ejecutar(Pedido $pedido): array
    {
        $faltantes = $this->analizarFaltantes->ejecutar($pedido);

        if ($faltantes === []) {
            return [];
        }

        $items = $pedido->items()
            ->whereIn('id', collect($faltantes)->pluck('pedido_item_id')->unique()->all())
            ->get()
            ->keyBy('id');

        collect($faltantes)
            ->groupBy('pedido_item_id')
            ->each(function (Collection $detalles, int|string $itemId) use ($items): void {
                $item = $items->get((int) $itemId);

                if (! $item instanceof PedidoItem || $item->estado !== EstadoItemPedido::PENDIENTE) {
                    return;
                }

                $item->estado = EstadoItemPedido::BLOQUEADO_STOCK;
                $item->bloqueo_stock_detalle = $detalles->values()->all();
                $item->bloqueado_stock_en = now();

                $this->repositorio->guardarItem($item);
            });

        $resumen = collect($faltantes)
            ->map(fn (array $faltante): string => sprintf(
                '%s: falta %s de %s',
                (string) $faltante['plato'],
                number_format((float) $faltante['faltante'], 2),
                (string) $faltante['ingrediente'],
            ))
            ->unique()
            ->take(4)
            ->implode('; ');

        throw new DomainException(
            'Pedido bloqueado por stock insuficiente. Confirme con el cliente si desea sustituir ingrediente, cambiar platillo, quitar el item o cancelar el pedido. '.$resumen
        );
    }
}
