<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\Actions\Limpieza\Lavanderia\FinalizarProcesosLavanderia;
use App\BusinessLogic\Inventario\Servicios\ServicioConsumos;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Shared\Stock as SharedStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class ReponerDesdeLavanderia
{
    public function __construct(
        private readonly ServicioConsumos $servicioConsumos,
        private readonly FinalizarProcesosLavanderia $finalizarProcesos,
    ) {}

    /**
     * @param  int|array<int>  $ubicacionLavanderiaId
     */
    public function execute(
        int $stockId,
        float $cantidad,
        int|array $ubicacionLavanderiaId,
        string $tipoDestino,
        int $destinoId,
        ?int $creadoPorId,
    ): void {
        if ($cantidad <= 0.0) {
            throw new \InvalidArgumentException('La cantidad a reponer debe ser mayor a cero.');
        }

        $stockableType = match ($tipoDestino) {
            'habitacion' => Habitacion::class,
            'espacio' => Espacio::class,
            'ubicacion' => Ubicacion::class,
            default => throw new \InvalidArgumentException("Tipo de destino inválido: {$tipoDestino}"),
        };

        DB::transaction(function () use ($stockId, $cantidad, $ubicacionLavanderiaId, $stockableType, $destinoId, $creadoPorId): void {
            $stock = Stock::query()
                ->with(['variante', 'lote'])
                ->whereKey($stockId)
                ->when(
                    is_array($ubicacionLavanderiaId),
                    fn ($query) => $query->whereIn('ubicacion_id', $ubicacionLavanderiaId),
                    fn ($query) => $query->where('ubicacion_id', $ubicacionLavanderiaId),
                )
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $stock->cantidad < $cantidad) {
                throw new \RuntimeException(sprintf(
                    'Stock insuficiente en lavandería. Disponible: %f, requerido: %f',
                    (float) $stock->cantidad,
                    $cantidad,
                ));
            }

            $ubicacionDestinoId = $this->resolverUbicacionDestino($stockableType, $destinoId);

            $detalle = $this->servicioConsumos->ejecutarConsumoDeStock(
                stock: $stock,
                cantidad: $cantidad,
                tipoMovimiento: 'TRASLADO',
                ubicacionDestinoId: $ubicacionDestinoId,
                creadoPorId: $creadoPorId,
                referencia: "Salida de lavandería hacia {$stockableType} #{$destinoId}",
                notas: 'Reposición de blancos/insumos desde lavandería.',
            );

            $cantidadConsumida = (float) $detalle['cantidad'];

            $existing = SharedStock::query()
                ->where('stockable_type', $stockableType)
                ->where('stockable_id', $destinoId)
                ->where('producto_variante_id', $stock->producto_variante_id)
                ->lockForUpdate()
                ->first();

            if (! $existing instanceof SharedStock) {
                $existing = new SharedStock([
                    'stockable_type' => $stockableType,
                    'stockable_id' => $destinoId,
                    'producto_variante_id' => $stock->producto_variante_id,
                ]);
            }

            $existing->cantidad_actual = (float) ($existing->cantidad_actual ?? 0) + $cantidadConsumida;
            if (! $existing->cantidad_ideal) {
                $existing->cantidad_ideal = $existing->cantidad_actual;
            }
            if ($stock->lote_id !== null) {
                $existing->lote_id = (int) $stock->lote_id;
            }
            $existing->save();

            $this->finalizarProcesos->execute(
                productoId: (int) $stock->producto_id,
                productoVarianteId: $stock->producto_variante_id !== null ? (int) $stock->producto_variante_id : null,
                loteId: $stock->lote_id !== null ? (int) $stock->lote_id : null,
                cantidad: $cantidadConsumida,
            );
        });
    }

    /** @param class-string<Model> $stockableType */
    private function resolverUbicacionDestino(string $stockableType, int $destinoId): ?int
    {
        if ($stockableType === Habitacion::class) {
            return Habitacion::query()->findOrFail($destinoId)->ubicacion_id;
        }

        if ($stockableType === Espacio::class) {
            return Espacio::query()->findOrFail($destinoId)->ubicacion_id;
        }

        Ubicacion::query()->findOrFail($destinoId);

        return $destinoId;
    }
}
