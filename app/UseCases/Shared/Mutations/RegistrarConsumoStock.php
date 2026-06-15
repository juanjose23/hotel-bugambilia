<?php

declare(strict_types=1);

namespace App\UseCases\Shared\Mutations;

use App\Models\Catalogos\ProductoVariante;
use App\Models\Inventario\MovimientoStock;
use App\Models\Shared\Stock;
use Illuminate\Support\Facades\DB;

class RegistrarConsumoStock
{
    public function execute(
        int $stockId,
        float $cantidad,
        string $motivo = 'consumo',
        ?int $creadoPorId = null,
        ?string $referencia = null,
    ): void {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a consumir debe ser mayor a cero.');
        }

        DB::transaction(function () use ($stockId, $cantidad, $motivo, $creadoPorId, $referencia) {
            $stock = Stock::with(['stockable', 'variante.producto'])->lockForUpdate()->findOrFail($stockId);
            /** @var ProductoVariante|null $variante */
            $variante = $stock->variante;
            $entidad = $stock->stockable;

            if (! $variante || ! $entidad) {
                throw new \RuntimeException('Registro de stock incompleto: faltan relaciones.');
            }

            $producto = $variante->producto;
            if (! $producto) {
                throw new \RuntimeException("Variante ID {$variante->id} no tiene producto asociado.");
            }

            if ((float) $stock->cantidad_actual < $cantidad) {
                throw new \RuntimeException(sprintf(
                    'Stock insuficiente. Actual: %s, Requerido: %s',
                    $stock->cantidad_actual,
                    $cantidad
                ));
            }

            $stock->cantidad_actual -= $cantidad;
            $stock->ultima_verificacion = now();
            $stock->save();

            MovimientoStock::create([
                'tipo' => 'CONSUMO',
                'lote_id' => $stock->lote_id,
                'producto_id' => $producto->id,
                'cantidad' => -$cantidad,
                'ubicacion_origen_id' => null,
                'ubicacion_destino_id' => null,
                'documento_tipo' => 'consumo_stock',
                'documento_id' => $stock->id,
                'referencia' => $referencia ?: sprintf(
                    'Consumo en %s: %s',
                    $entidad::class,
                    $motivo
                ),
                'creado_por_id' => $creadoPorId,
                'notas' => $motivo,
            ]);
        });
    }
}
