<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Lavanderia;

use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Limpieza\LavanderiaProceso;
use Illuminate\Support\Facades\DB;

final class RegistrarEntradaDirectaLavanderia
{
    public function execute(
        int $productoVarianteId,
        float $cantidad,
        int $ubicacionLavanderiaId,
        ?int $creadoPorId,
        ?string $notas = null,
    ): void {
        if ($cantidad <= 0.0) {
            throw new \InvalidArgumentException('La cantidad a ingresar debe ser mayor a cero.');
        }

        DB::transaction(function () use ($productoVarianteId, $cantidad, $ubicacionLavanderiaId, $creadoPorId, $notas): void {
            $variante = ProductoVariante::query()
                ->with('producto')
                ->whereKey($productoVarianteId)
                ->firstOrFail();

            $stock = Stock::query()
                ->where('producto_id', $variante->producto_id)
                ->where('producto_variante_id', $productoVarianteId)
                ->whereNull('lote_id')
                ->where('ubicacion_id', $ubicacionLavanderiaId)
                ->lockForUpdate()
                ->first();

            if ($stock instanceof Stock) {
                $stock->cantidad = (float) $stock->cantidad + $cantidad;
                $stock->save();
            } else {
                Stock::query()->create([
                    'producto_id' => $variante->producto_id,
                    'producto_variante_id' => $productoVarianteId,
                    'lote_id' => null,
                    'ubicacion_id' => $ubicacionLavanderiaId,
                    'cantidad' => $cantidad,
                ]);
            }

            LavanderiaProceso::query()->create([
                'producto_id' => $variante->producto_id,
                'producto_variante_id' => $productoVarianteId,
                'lote_id' => null,
                'cantidad' => $cantidad,
                'estado' => 'en_proceso',
            ]);

            MovimientoStock::query()->create([
                'tipo' => 'ENTRADA_LAVANDERIA',
                'lote_id' => null,
                'producto_id' => $variante->producto_id,
                'cantidad' => $cantidad,
                'ubicacion_origen_id' => null,
                'ubicacion_destino_id' => $ubicacionLavanderiaId,
                'documento_tipo' => 'lavanderia',
                'referencia' => 'Entrada directa a lavandería',
                'creado_por_id' => $creadoPorId,
                'notas' => $notas,
            ]);
        });
    }
}
