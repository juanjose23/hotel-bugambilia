<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Lavanderia;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\ProductoVariante;

final class ObtenerOpcionesInsumosQuimicos
{
    /**
     * @return array<int, string>
     */
    public function execute(): array
    {
        /** @var array<int, string> */
        return ProductoVariante::query()
            ->with(['producto.categoria', 'producto.unidadMedida'])
            ->whereHas('producto', function ($q): void {
                $q->where('estado', EstadoGeneral::Activo);
            })
            ->get()
            ->mapWithKeys(function (ProductoVariante $variante): array {
                $producto = $variante->producto;
                $nombreProducto = $producto !== null ? (string) $producto->nombre : 'Insumo';
                $categoria = $producto?->categoria?->nombre;
                $unidad = $producto?->unidadMedida?->nombre;

                $label = $nombreProducto;
                if ($variante->nombre_variante && trim($variante->nombre_variante) !== '' && $variante->nombre_variante !== 'Estándar') {
                    $label .= " ({$variante->nombre_variante})";
                }
                if ($categoria) {
                    $label .= " · [{$categoria}]";
                }
                if ($unidad) {
                    $label .= " · [{$unidad}]";
                }

                return [(int) $variante->id => $label];
            })
            ->all();
    }
}
