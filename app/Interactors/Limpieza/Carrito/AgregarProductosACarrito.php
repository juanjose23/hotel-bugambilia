<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\Stock;
use Illuminate\Support\Facades\DB;

class AgregarProductosACarrito
{
    /**
     * Agrega productos/suministros directamente a un carro.
     */
    public function execute(int $carritoId, int $productoId, float $cantidad, ?int $productoVarianteId = null, ?int $loteId = null): void
    {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a agregar debe ser mayor a cero.');
        }

        if ($productoVarianteId === null) {

            // Si no hay variante, buscar si existe alguna por defecto o usar null
            $productoVarianteId = null;
        } else {
            $variante = ProductoVariante::find($productoVarianteId);
            if ($variante) {
                $productoId = $variante->producto_id;
            }
        }

        DB::transaction(function () use ($carritoId, $productoId, $productoVarianteId, $cantidad, $loteId) {
            $stock = Stock::where([
                'ubicacion_id' => $carritoId,
                'producto_id' => $productoId,
                'producto_variante_id' => $productoVarianteId,
                'lote_id' => $loteId,
            ])->first();

            if ($stock) {
                $stock->cantidad += $cantidad;
                $stock->save();
            } else {
                Stock::create([
                    'ubicacion_id' => $carritoId,
                    'producto_id' => $productoId,
                    'producto_variante_id' => $productoVarianteId,
                    'lote_id' => $loteId,
                    'cantidad' => $cantidad,
                ]);
            }
        });
    }
}
