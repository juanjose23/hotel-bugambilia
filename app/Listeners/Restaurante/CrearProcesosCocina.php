<?php

declare(strict_types=1);

namespace App\Listeners\Restaurante;

use App\Events\Restaurante\PedidoEnviadoACocina;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;

final class CrearProcesosCocina
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
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
            $this->crearProcesoParaItem($item, $pedido->id, $indice);
            $indice++;
        }
    }

    private function crearProcesoParaItem(PedidoItem $item, int $pedidoId, int $indice): void
    {
        $plato = $item->plato;

        if ($plato === null) {
            return;
        }

        $productoReceta = $plato->receta;

        if ($productoReceta === null) {
            return;
        }

        $cantidadPlatos = max((int) $item->cantidad, 1);
        $ingredientes = $this->repositorio->obtenerIngredientesReceta($productoReceta->id);
        $cocina = $this->repositorio->obtenerUbicacionPorNombre('Cocina Restaurante');
        $cocinaId = $cocina?->id;

        $costoTotal = 0.0;
        $itemsData = [];

        foreach ($ingredientes as $ingrediente) {
            $variante = $ingrediente->variante;
            $productoDestino = $variante?->producto;
            $cantidadIngrediente = (float) $ingrediente->cantidad * $cantidadPlatos;

            $costoUnitario = 0.0;
            if ($cocinaId && $variante) {
                $stock = $this->repositorio->obtenerStockConLote($cocinaId, $variante->id);

                if ($stock && $stock->lote?->costo_unitario) {
                    $costoUnitario = (float) $stock->lote->costo_unitario;
                }
            }

            $costoAsignado = round($costoUnitario * $cantidadIngrediente, 2);
            $costoTotal += $costoAsignado;

            $itemsData[] = [
                'ingrediente' => $ingrediente,
                'variante' => $variante,
                'productoDestino' => $productoDestino,
                'cantidadIngrediente' => $cantidadIngrediente,
                'costoAsignado' => $costoAsignado,
            ];
        }

        $proceso = new ProcesoCocina([
            'codigo' => $this->generarCodigo($pedidoId, $plato->id, $indice),
            'plato_id' => $plato->id,
            'cantidad_platos' => $cantidadPlatos,
            'producto_origen_id' => $productoReceta->id,
            'cantidad_procesada' => $cantidadPlatos,
            'costo_total' => $costoTotal,
            'observaciones' => $item->observaciones,
        ]);

        $this->repositorio->guardarProcesoCocina($proceso);

        foreach ($itemsData as $itemData) {
            $this->repositorio->guardarProcesoItem($proceso, [
                'producto_destino_id' => $itemData['productoDestino'] !== null
                    ? $itemData['productoDestino']->id
                    : $itemData['ingrediente']->producto_padre_id,
                'variante_destino_id' => $itemData['variante']?->id,
                'cantidad' => $itemData['cantidadIngrediente'],
                'peso_unitario' => $itemData['variante']?->peso,
                'peso_total' => $itemData['cantidadIngrediente'],
                'costo_asignado' => $itemData['costoAsignado'],
                'es_merma' => false,
                'ubicacion_destino_id' => null,
            ]);
        }
    }

    private function generarCodigo(int $pedidoId, int $platoId, int $indice): string
    {
        $timestamp = now()->format('His');
        $pos = str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT);

        return "PC-{$pedidoId}-{$platoId}-{$pos}-{$timestamp}";
    }
}
