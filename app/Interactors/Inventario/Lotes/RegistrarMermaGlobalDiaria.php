<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Lotes;

use App\BusinessLogic\Inventario\Estrategias\FEFOStrategy;
use App\BusinessLogic\Inventario\Servicios\ServicioMermas;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Queries\Inventario\Stock\ObtenerStockParaConsumo;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class RegistrarMermaGlobalDiaria
{
    public function __construct(
        private readonly ObtenerStockParaConsumo $stockQuery,
        private readonly ServicioMermas $servicioMermas,
        private readonly FEFOStrategy $fefo,
    ) {}

    /**
     * @param  array<int, array{producto_id: int, cantidad: float, motivo?: string|null}>  $items
     * @return array{total_items: int, total_perdida: float, detalle: list<array{producto_id: int, cantidad: float, costo_total: float|null, motivo: string}>}
     */
    public function ejecutar(
        string $fecha,
        int $ubicacionId,
        array $items,
        ?int $usuarioId = null,
    ): array {
        $ubicacion = Ubicacion::find($ubicacionId);
        if ($ubicacion === null) {
            throw new DomainException('Ubicación no encontrada.');
        }

        $referenciaBase = "Merma Global {$fecha}";
        $detalle = [];
        $totalPerdida = 0.0;

        DB::transaction(function () use ($items, $ubicacionId, $usuarioId, $referenciaBase, &$detalle, &$totalPerdida): void {
            foreach ($items as $item) {
                $productoId = (int) $item['producto_id'];
                $cantidad = (float) $item['cantidad'];
                $motivo = $item['motivo'] ?? null;

                if ($productoId <= 0 || $cantidad <= 0) {
                    continue;
                }

                $stocks = $this->stockQuery->ejecutar($productoId, $ubicacionId);

                if ($stocks->isEmpty()) {
                    throw new DomainException("No hay stock disponible del producto ID {$productoId} en la ubicación seleccionada.");
                }

                $loteModels = array_values(array_filter($stocks->pluck('lote')->all(), fn (mixed $item): bool => $item instanceof Lote));
                $lotes = new Collection($loteModels);

                $seleccion = $this->fefo->seleccionarLotes($lotes, $cantidad);
                $totalAsignado = array_sum(array_column($seleccion, 'cantidad'));

                if ($totalAsignado < $cantidad) {
                    throw new DomainException("Stock insuficiente para producto ID {$productoId}. Requerido: {$cantidad}, disponible: {$totalAsignado}.");
                }

                $costoTotalItem = 0.0;

                foreach ($seleccion as $asignacion) {
                    $lote = $asignacion['lote'];
                    $cantMerma = $asignacion['cantidad'];

                    $this->servicioMermas->ejecutarMerma(
                        lote: $lote,
                        cantidad: $cantMerma,
                        motivo: $referenciaBase.($motivo ? " - {$motivo}" : ''),
                        creadoPorId: $usuarioId,
                    );

                    $costoTotalItem += $lote->costo_unitario !== null
                        ? $lote->costo_unitario * $cantMerma
                        : 0.0;
                }

                $totalPerdida += $costoTotalItem;
                $detalle[] = [
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'costo_total' => $costoTotalItem > 0 ? $costoTotalItem : null,
                    'motivo' => $motivo ?? 'Sin motivo',
                ];
            }
        });

        return [
            'total_items' => count($detalle),
            'total_perdida' => $totalPerdida,
            'detalle' => $detalle,
        ];
    }
}
