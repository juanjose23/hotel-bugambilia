<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\Interactors\Inventario\ConsumirStock;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SustitucionStock as SustitucionStockModel;
use App\Repository\Models\Shared\Stock as SharedStock;

class ProcesarSustitucionesLimpieza
{
    public function __construct(
        private readonly ConsumirStock $consumirStock,
    ) {}

    /** @param array<string, mixed> $data */
    public function ejecutar(LimpiezaEjecucion $ejecucion, array $data, ?int $carritoId, ?int $usuarioId): void
    {
        /** @var list<array{ producto_variante_id: int|string, sustituto_variante_id: int|string, cantidad: int|float|string }> $sustituciones */
        $sustituciones = $data['sustituciones'] ?? [];
        foreach ($sustituciones as $sub) {
            $originalVarId = (int) $sub['producto_variante_id'];
            $sustitutoVarId = (int) $sub['sustituto_variante_id'];
            $qty = (float) $sub['cantidad'];

            if (! $originalVarId || ! $sustitutoVarId || $qty <= 0) {
                continue;
            }

            $originalVar = ProductoVariante::findOrFail($originalVarId);
            $sustitutoVar = ProductoVariante::findOrFail($sustitutoVarId);

            SustitucionStockModel::create([
                'ejecucion_id' => $ejecucion->id,
                'producto_id' => $originalVar->producto_id,
                'sustituto_producto_id' => $sustitutoVar->producto_id,
                'producto_variante_id' => $originalVarId,
                'sustituto_variante_id' => $sustitutoVarId,
                'cantidad' => $qty,
            ]);

            if ($carritoId) {
                $available = (float) InventarioStock::where('ubicacion_id', $carritoId)
                    ->where('producto_variante_id', $sustitutoVarId)
                    ->sum('cantidad');

                if ($available < $qty) {
                    throw new \RuntimeException(sprintf(
                        'El carrito no cuenta con stock suficiente del producto sustituto "%s". Requerido para sustitución: %f, Disponible en Carro: %f. No se puede realizar la sustitución.',
                        $sustitutoVar->producto->nombre ?? 'Sustituto',
                        $qty,
                        $available
                    ));
                }

                $this->consumirStock->execute(
                    productoId: $sustitutoVar->producto_id,
                    cantidadRequerida: $qty,
                    ubicacionId: $carritoId,
                    tipoMovimiento: 'TRASLADO',
                    productoVarianteId: $sustitutoVarId,
                    documentoId: $ejecucion->id,
                    documentoTipo: 'limp_ejecuciones',
                    creadoPorId: $usuarioId,
                    referencia: "Sustitución en ejecución #{$ejecucion->id}",
                    notas: "Sustituye variante #{$originalVarId} por variante #{$sustitutoVarId}"
                );

                $sharedStock = SharedStock::where([
                    'stockable_type' => $ejecucion->limpiable_type,
                    'stockable_id' => $ejecucion->limpiable_id,
                    'producto_variante_id' => $originalVarId,
                ])->first();

                if ($sharedStock) {
                    $sharedStock->cantidad_actual = (float) $sharedStock->cantidad_actual + $qty;
                    $sharedStock->save();
                }
            }
        }
    }
}
