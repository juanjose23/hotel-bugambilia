<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\CotizacionItem;
use App\Repository\Models\Compras\OrdenCompra;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class OrdenCompraRepositorio implements OrdenCompraRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): OrdenCompra
    {
        $orden = OrdenCompra::create($datos);

        return $orden;
    }

    /**
     * @param  Collection<int, CotizacionItem>  $itemsElegidos
     * @param  array{subtotal: float, impuestos: float, total: float}  $totales
     */
    public function crearConItems(
        Cotizacion $cotizacion,
        Collection $itemsElegidos,
        string $codigo,
        array $totales,
        string $notas
    ): OrdenCompra {
        return DB::transaction(function () use ($cotizacion, $itemsElegidos, $codigo, $totales, $notas): OrdenCompra {
            $orden = $this->crear([
                'codigo' => $codigo,
                'proveedor_id' => $cotizacion->proveedor_id,
                'solicitud_id' => $cotizacion->solicitud_id,
                'cotizacion_id' => $cotizacion->id,
                'fecha_orden' => now(),
                'condicion_pago_id' => $cotizacion->condicion_pago_id,
                'subtotal' => $totales['subtotal'],
                'impuestos' => $totales['impuestos'],
                'total' => $totales['total'],
                'estado' => EstadoOrdenCompra::Borrador,
                'notas' => $notas,
            ]);

            foreach ($itemsElegidos as $item) {
                $solicitudItem = $cotizacion->solicitud?->items
                    ->where('producto_id', $item->producto_id)
                    ->where('producto_variante_id', $item->producto_variante_id)
                    ->first();

                $orden->items()->create([
                    'producto_id' => $item->producto_id,
                    'producto_variante_id' => $item->producto_variante_id,
                    'unidad_medida_id' => $solicitudItem?->unidad_medida_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->subtotal,
                ]);
            }

            return $orden;
        });
    }

    public function actualizarEstado(OrdenCompra $orden, EstadoOrdenCompra $estado): void
    {
        $orden->update(['estado' => $estado]);
    }
}
