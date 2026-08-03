<?php

declare(strict_types=1);

namespace App\Listeners\Restaurante;

use App\Events\Restaurante\PedidoEnviadoACocina;
use App\Interactors\Restaurante\Cocina\RegistrarProcesoCocina;
use App\Repository\Models\Restaurante\PedidoItem;

final class CrearProcesosCocina
{
    public function __construct(
        private readonly RegistrarProcesoCocina $registrarProceso,
    ) {}

    public function handle(PedidoEnviadoACocina $event): void
    {
        $pedido = $event->pedido;
        $pedido->loadMissing(['items.plato.receta']);

        $itemsNuevos = $pedido->items->filter(
            fn (PedidoItem $item): bool => in_array($item->id, $event->itemIds, strict: true),
        );

        $indice = 0;

        foreach ($itemsNuevos as $item) {
            $plato = $item->plato;

            if ($plato === null) {
                continue;
            }

            if ($plato->receta === null) {
                continue;
            }

            $this->registrarProceso->ejecutar([
                'codigo' => $this->generarCodigo($pedido->id, $plato->id, $indice),
                'plato_id' => $plato->id,
                'cantidad_platos' => max((int) $item->cantidad, 1),
                'observaciones' => $item->observaciones,
            ]);

            $indice++;
        }
    }

    private function generarCodigo(int $pedidoId, int $platoId, int $indice): string
    {
        $timestamp = now()->format('His');
        $pos = str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT);

        return "PC-{$pedidoId}-{$platoId}-{$pos}-{$timestamp}";
    }
}
