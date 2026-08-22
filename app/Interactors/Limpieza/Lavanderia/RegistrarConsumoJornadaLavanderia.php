<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\Actions\Limpieza\Lavanderia\FinalizarProcesosLavanderia;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Limpieza\Turno;
use Illuminate\Support\Facades\DB;

final class RegistrarConsumoJornadaLavanderia
{
    public function __construct(
        private readonly FinalizarProcesosLavanderia $finalizarProcesos,
    ) {}

    /**
     * @param  int|array<int>  $ubicacionLavanderiaId
     * @param  list<array{stock_id: int, cantidad: float, notas?: string|null}>  $insumos
     * @param  list<array{stock_id: int, cantidad: float, notas?: string|null}>  $mermas
     * @return array{total_insumos: int, total_cantidad: float, total_mermas: int}
     */
    public function execute(
        int|array $ubicacionLavanderiaId,
        string $fechaJornada,
        string|int $turno,
        array $insumos,
        ?string $operadorNombre = null,
        ?float $kilosLavados = null,
        ?int $cargasLavadas = null,
        ?int $creadoPorId = null,
        ?string $observacionesGenerales = null,
        array $mermas = [],
    ): array {
        $insumosValidos = array_filter($insumos, fn (array $item): bool => $item['stock_id'] > 0 && $item['cantidad'] > 0.0);

        if (empty($insumosValidos)) {
            throw new \InvalidArgumentException('Debe seleccionar al menos un insumo con cantidad mayor a cero.');
        }

        $mermasValidas = array_filter($mermas, fn (array $item): bool => $item['stock_id'] > 0 && $item['cantidad'] > 0.0);

        return DB::transaction(function () use (
            $ubicacionLavanderiaId,
            $fechaJornada,
            $turno,
            $insumosValidos,
            $operadorNombre,
            $kilosLavados,
            $cargasLavadas,
            $creadoPorId,
            $observacionesGenerales,
            $mermasValidas
        ): array {
            $totalInsumos = 0;
            $totalCantidad = 0.0;
            $totalMermas = 0;

            $turnoLabel = 'Turno';
            if (is_numeric($turno) && (int) $turno > 0) {
                $turnoObj = Turno::query()->find((int) $turno);
                $turnoLabel = $turnoObj !== null ? (string) $turnoObj->nombre : "Turno #{$turno}";
            } else {
                $turnoLabel = ucfirst((string) $turno);
            }

            $referenciaBase = "Consumo Jornada {$turnoLabel} - {$fechaJornada}";

            if ($operadorNombre !== null && trim($operadorNombre) !== '') {
                $referenciaBase .= " (Operador: {$operadorNombre})";
            }

            if ($kilosLavados !== null && $kilosLavados > 0.0) {
                $referenciaBase .= " - {$kilosLavados} kg";
            } elseif ($cargasLavadas !== null && $cargasLavadas > 0) {
                $referenciaBase .= " - {$cargasLavadas} cargas";
            }

            // 1. Procesar Insumos Químicos
            foreach ($insumosValidos as $item) {
                $stockId = (int) $item['stock_id'];
                $cantidad = (float) $item['cantidad'];
                $notasItem = isset($item['notas']) && trim((string) $item['notas']) !== ''
                    ? trim((string) $item['notas'])
                    : $observacionesGenerales;

                $stock = Stock::query()
                    ->with(['lote', 'variante.producto'])
                    ->whereKey($stockId)
                    ->when(
                        is_array($ubicacionLavanderiaId),
                        fn ($query) => $query->whereIn('ubicacion_id', $ubicacionLavanderiaId),
                        fn ($query) => $query->where('ubicacion_id', $ubicacionLavanderiaId),
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((float) $stock->cantidad < $cantidad) {
                    $producto = $stock->variante?->producto;
                    $productoNombre = $producto !== null ? (string) $producto->nombre : 'Insumo';
                    throw new \RuntimeException(sprintf(
                        'Stock insuficiente de "%s" en lavandería. Disponible: %.2f, Requerido: %.2f',
                        $productoNombre,
                        (float) $stock->cantidad,
                        $cantidad
                    ));
                }

                $stock->cantidad = (float) $stock->cantidad - $cantidad;
                $stock->save();

                $costoUnitario = $stock->lote?->costo_unitario !== null ? (float) $stock->lote->costo_unitario : null;
                $costoTotal = $costoUnitario !== null ? $costoUnitario * $cantidad : null;
                $ubicacionId = (int) $stock->ubicacion_id;

                MovimientoStock::query()->create([
                    'tipo' => 'CONSUMO_LAVANDERIA',
                    'lote_id' => $stock->lote_id,
                    'producto_id' => $stock->producto_id,
                    'cantidad' => -$cantidad,
                    'costo_unitario' => $costoUnitario,
                    'costo_total' => $costoTotal,
                    'ubicacion_origen_id' => $ubicacionId,
                    'ubicacion_destino_id' => null,
                    'documento_tipo' => 'jornada_lavanderia',
                    'referencia' => $referenciaBase,
                    'creado_por_id' => $creadoPorId,
                    'notas' => $notasItem,
                ]);

                $totalInsumos++;
                $totalCantidad += $cantidad;
            }

            // 2. Procesar Mermas / Bajas del Turno (si hubo)
            foreach ($mermasValidas as $mermaItem) {
                $mermaStockId = (int) $mermaItem['stock_id'];
                $mermaCantidad = (float) $mermaItem['cantidad'];
                $motivoMerma = isset($mermaItem['notas']) && trim((string) $mermaItem['notas']) !== ''
                    ? trim((string) $mermaItem['notas'])
                    : 'Baja/Merma reportada en cierre de turno';

                $stockMerma = Stock::query()
                    ->with(['lote', 'variante.producto'])
                    ->whereKey($mermaStockId)
                    ->when(
                        is_array($ubicacionLavanderiaId),
                        fn ($query) => $query->whereIn('ubicacion_id', $ubicacionLavanderiaId),
                        fn ($query) => $query->where('ubicacion_id', $ubicacionLavanderiaId),
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((float) $stockMerma->cantidad < $mermaCantidad) {
                    $producto = $stockMerma->variante?->producto;
                    $productoNombre = $producto !== null ? (string) $producto->nombre : 'Pieza';
                    throw new \RuntimeException(sprintf(
                        'Stock insuficiente de "%s" para dar de baja en lavandería. Disponible: %.2f, Requerido: %.2f',
                        $productoNombre,
                        (float) $stockMerma->cantidad,
                        $mermaCantidad
                    ));
                }

                $stockMerma->cantidad = (float) $stockMerma->cantidad - $mermaCantidad;
                $stockMerma->save();

                $costoUnitario = $stockMerma->lote?->costo_unitario !== null ? (float) $stockMerma->lote->costo_unitario : null;
                $costoTotal = $costoUnitario !== null ? $costoUnitario * $mermaCantidad : null;
                $ubicacionId = (int) $stockMerma->ubicacion_id;

                MovimientoStock::query()->create([
                    'tipo' => 'CONSUMO_LAVANDERIA',
                    'lote_id' => $stockMerma->lote_id,
                    'producto_id' => $stockMerma->producto_id,
                    'cantidad' => -$mermaCantidad,
                    'costo_unitario' => $costoUnitario,
                    'costo_total' => $costoTotal,
                    'ubicacion_origen_id' => $ubicacionId,
                    'ubicacion_destino_id' => null,
                    'documento_tipo' => 'merma_jornada_lavanderia',
                    'referencia' => "Merma en {$referenciaBase}",
                    'creado_por_id' => $creadoPorId,
                    'notas' => $motivoMerma,
                ]);

                $this->finalizarProcesos->execute(
                    productoId: (int) $stockMerma->producto_id,
                    productoVarianteId: $stockMerma->producto_variante_id !== null ? (int) $stockMerma->producto_variante_id : null,
                    loteId: $stockMerma->lote_id !== null ? (int) $stockMerma->lote_id : null,
                    cantidad: $mermaCantidad,
                );

                $totalMermas += (int) $mermaCantidad;
            }

            return [
                'total_insumos' => $totalInsumos,
                'total_cantidad' => $totalCantidad,
                'total_mermas' => $totalMermas,
            ];
        });
    }
}
