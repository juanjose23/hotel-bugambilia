<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Lavanderia;

use App\Repository\Models\Catalogos\ProductoVariante;

final class ObtenerOpcionesBlancosLavanderia
{
    /**
     * @return array<int, string>
     */
    public function execute(): array
    {
        $terminosLavanderia = [
            'blanco',
            'blancos',
            'lavander',
            'sábana',
            'sabana',
            'toalla',
            'cortina',
            'mantel',
            'manteler',
            'funda',
            'colcha',
            'edredón',
            'edredon',
            'almohada',
            'bata',
        ];

        $opciones = ProductoVariante::query()
            ->with('producto.categoria')
            ->whereHas('producto', function ($query) use ($terminosLavanderia): void {
                $query->where(function ($productoQuery) use ($terminosLavanderia): void {
                    foreach ($terminosLavanderia as $termino) {
                        $productoQuery
                            ->orWhereRaw('LOWER(nombre) LIKE ?', ['%'.mb_strtolower($termino).'%'])
                            ->orWhereRaw('LOWER(COALESCE(descripcion, \'\')) LIKE ?', ['%'.mb_strtolower($termino).'%']);
                    }
                })
                    ->orWhereHas('categoria', function ($categoriaQuery) use ($terminosLavanderia): void {
                        $categoriaQuery->where(function ($categoriaNombreQuery) use ($terminosLavanderia): void {
                            foreach ($terminosLavanderia as $termino) {
                                $categoriaNombreQuery
                                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%'.mb_strtolower($termino).'%'])
                                    ->orWhereRaw('LOWER(COALESCE(descripcion, \'\')) LIKE ?', ['%'.mb_strtolower($termino).'%']);
                            }
                        });
                    });
            })
            ->orderBy('producto_id')
            ->orderBy('nombre_variante')
            ->get()
            ->mapWithKeys(function (ProductoVariante $variante): array {
                $producto = $variante->producto;
                $nombreProducto = $producto !== null ? (string) $producto->nombre : 'Producto';
                $nombre = trim($nombreProducto.' '.($variante->nombre_variante ? "({$variante->nombre_variante})" : ''));

                return [(int) $variante->id => $nombre];
            })
            ->toArray();

        /** @var array<int, string> $opciones */
        return $opciones;
    }
}
