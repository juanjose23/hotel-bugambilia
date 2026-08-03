<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\BusinessLogic\Inventario\Estrategias\FEFOStrategy;
use App\BusinessLogic\Restaurante\Cocina\CalcularCostoProcesoCocina;
use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Inventario\Stock\ObtenerStockParaConsumo;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

final class ProcesarProcesoCocina
{
    public function __construct(
        private readonly CalcularCostoProcesoCocina $calcularCosto,
        private readonly ObtenerStockParaConsumo $stockQuery,
        private readonly FEFOStrategy $fefo,
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function guardar(?ProcesoCocina $proceso, array $data, ?int $usuarioId = null): ProcesoCocina
    {
        return DB::transaction(function () use ($proceso, $data, $usuarioId): ProcesoCocina {
            $platoId = is_numeric($data['plato_id'] ?? null) ? (int) $data['plato_id'] : null;
            $cantPlatos = is_numeric($data['cantidad_platos'] ?? null) ? (int) $data['cantidad_platos'] : 1;
            $obs = is_string($data['observaciones'] ?? null) ? $data['observaciones'] : null;

            $itemsDataRaw = is_array($data['items'] ?? null) ? $data['items'] : [];
            $itemsData = [];
            foreach ($itemsDataRaw as $raw) {
                if (is_array($raw)) {
                    $itemsData[] = $raw;
                }
            }
            $resulCostos = $this->calcularCosto->calcularDesdeArray($cantPlatos, $itemsData);

            if ($proceso === null) {
                $codigo = 'PRC-'.now()->format('Ymd-His');
                $proceso = $this->repositorio->crearProcesoCocina([
                    'codigo' => $codigo,
                    'plato_id' => $platoId,
                    'cantidad_platos' => $cantPlatos,
                    'cantidad_procesada' => $cantPlatos,
                    'costo_total' => $resulCostos['costo_total'],
                    'observaciones' => $obs,
                    'realizado_por' => $usuarioId,
                ]);
            } else {
                $this->repositorio->actualizarProcesoCocina($proceso, [
                    'plato_id' => $platoId,
                    'cantidad_platos' => $cantPlatos,
                    'cantidad_procesada' => $cantPlatos,
                    'costo_total' => $resulCostos['costo_total'],
                    'observaciones' => $obs,
                ]);

                $this->repositorio->eliminarItemsDeProcesoCocina($proceso);
            }

            $cocina = $this->repositorio->obtenerUbicacionPorNombre(UbicacionCocina::RESTAURANTE->value);
            $cocinaId = $cocina instanceof Ubicacion ? (int) $cocina->id : null;

            foreach ($itemsData as $itemData) {
                $rawProductoId = $itemData['producto_destino_id'] ?? null;
                $productoId = is_numeric($rawProductoId) ? (int) $rawProductoId : 0;
                if ($productoId <= 0) {
                    continue;
                }

                $cant = is_numeric($itemData['cantidad'] ?? null) ? (float) $itemData['cantidad'] : 1.0;
                $pesoUnit = is_numeric($itemData['peso_unitario'] ?? null) ? (float) $itemData['peso_unitario'] : null;
                $pesoTot = is_numeric($itemData['peso_total'] ?? null) ? (float) $itemData['peso_total'] : null;
                $esMerma = ! empty($itemData['es_merma']);
                $costoAsignado = is_numeric($itemData['costo_asignado'] ?? null) ? (float) $itemData['costo_asignado'] : 0.0;

                if ($costoAsignado <= 0.0 && $cocinaId !== null) {
                    $stocks = $this->stockQuery->ejecutar($productoId, $cocinaId);

                    if ($stocks->isNotEmpty()) {
                        $lotesRaw = $stocks->pluck('lote')->filter(fn ($l) => $l instanceof Lote);

                        if ($lotesRaw->isNotEmpty()) {
                            /** @var array<int, Lote> $lotesArray */
                            $lotesArray = $lotesRaw->values()->all();
                            $loteCollection = new EloquentCollection($lotesArray);
                            $seleccion = $this->fefo->seleccionarLotes($loteCollection, 1.0);

                            if ($seleccion !== []) {
                                $costoUnitario = $seleccion[0]['lote']->costo_unitario ?? 0.0;
                                $costoAsignado = round($cant * (float) $costoUnitario, 2);
                            }
                        }
                    }
                }

                $this->repositorio->guardarProcesoItem($proceso, [
                    'producto_destino_id' => $productoId,
                    'cantidad' => $cant,
                    'peso_unitario' => $pesoUnit,
                    'peso_total' => $pesoTot,
                    'es_merma' => $esMerma,
                    'costo_asignado' => $costoAsignado,
                ]);
            }

            return $this->repositorio->recalcularCostoTotalProceso($proceso);
        });
    }
}
