<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final readonly class RegistrarProcesoCocina
{
    public function __construct(
        private RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * Registra un proceso de cocina basado en la receta de un plato.
     * El costo se obtiene del Stock en Cocina Restaurante â†’ Lote â†’ costo_unitario.
     *
     * @param  array{codigo: string, plato_id: int, cantidad_platos: int, pedido_item_id?: int, realizado_por?: int|null, observaciones?: string|null}  $data
     */
    public function ejecutar(array $data): ProcesoCocina
    {
        return DB::transaction(function () use ($data): ProcesoCocina {
            $plato = $this->repositorio->obtenerPlatoConReceta($data['plato_id']);

            if (! $plato) {
                throw new RuntimeException("El plato [{$data['plato_id']}] no existe.");
            }

            $productoReceta = $plato->receta;

            if (! $productoReceta) {
                throw new RuntimeException("El plato [{$plato->nombre}] no tiene una receta asociada.");
            }

            $cantidadPlatos = max($data['cantidad_platos'], 1);
            $ingredientes = $this->repositorio->obtenerIngredientesReceta($productoReceta->id);
            $cocina = $this->repositorio->obtenerUbicacionPorNombre(UbicacionCocina::RESTAURANTE->value);
            $cocinaId = $cocina?->id;

            $costoTotal = 0.0;
            $itemsData = [];

            foreach ($ingredientes as $ingrediente) {
                $variante = $ingrediente->variante;
                $productoDestino = $variante?->producto;
                $rendimiento = ((float) ($productoReceta->rendimiento_porciones ?? 1) > 0 ? (float) $productoReceta->rendimiento_porciones : 1.0);
                $cantidadIngrediente = round(((float) $ingrediente->cantidad / $rendimiento) * $cantidadPlatos, 4);

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
                    'costoUnitario' => $costoUnitario,
                    'costoAsignado' => $costoAsignado,
                ];
            }

            if (isset($data['pedido_item_id'])) {
                $movimientos = $this->repositorio->consumosDePartida($data['pedido_item_id']);
                $costoTotal = 0.0;
                foreach ($movimientos as $movimiento) {
                    $costoTotal += (is_numeric($movimiento->getAttribute('costo_total')) ? (float) $movimiento->getAttribute('costo_total') : 0.0);
                }
            }

            $proceso = new ProcesoCocina([
                'codigo' => $data['codigo'],
                'plato_id' => $plato->id,
                'pedido_item_id' => $data['pedido_item_id'] ?? null,
                'tipo_registro' => isset($data['pedido_item_id']) ? 'consumo_pedido' : 'costeo',
                'cantidad_platos' => $cantidadPlatos,
                'producto_origen_id' => $productoReceta->id,
                'cantidad_procesada' => $cantidadPlatos,
                'costo_total' => $costoTotal,
                'realizado_por' => $data['realizado_por'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            $this->repositorio->guardarProcesoCocina($proceso);

            if (isset($data['pedido_item_id'])) {
                foreach ($this->repositorio->consumosDePartida($data['pedido_item_id']) as $movimiento) {
                    $this->repositorio->guardarProcesoItem($proceso, [
                        'producto_destino_id' => $movimiento->producto_id,
                        'variante_destino_id' => $movimiento->lote?->producto_variante_id,
                        'cantidad' => abs((float) $movimiento->cantidad),
                        'peso_unitario' => null, 'peso_total' => null,
                        'costo_asignado' => (is_numeric($movimiento->getAttribute('costo_total')) ? (float) $movimiento->getAttribute('costo_total') : 0.0),
                        'es_merma' => false, 'ubicacion_destino_id' => null,
                    ]);
                }
            } else {
                foreach ($itemsData as $item) {
                    $ingrediente = $item['ingrediente'];
                    $variante = $item['variante'];
                    $productoDestino = $item['productoDestino'];
                    $cantidadIngrediente = $item['cantidadIngrediente'];

                    $this->repositorio->guardarProcesoItem($proceso, [
                        'producto_destino_id' => $productoDestino !== null ? $productoDestino->id : $ingrediente->producto_padre_id,
                        'variante_destino_id' => $variante?->id,
                        'cantidad' => $cantidadIngrediente,
                        'peso_unitario' => $variante?->peso,
                        'peso_total' => $cantidadIngrediente,
                        'costo_asignado' => $item['costoAsignado'],
                        'es_merma' => false,
                        'ubicacion_destino_id' => null,
                    ]);

                }
            }

            return $proceso;
        });
    }
}
