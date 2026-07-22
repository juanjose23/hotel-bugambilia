<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;

final class ObtenerOpcionesCatalogos
{
    /** @return array<int, string> */
    public function opcionesPorTipo(string $codigoCatalogoTipo): array
    {
        $catalogos = Catalogo::whereHas(
            'catalogoTipo',
            fn ($q) => $q->where('codigo', $codigoCatalogoTipo)
        )->get();

        $result = [];
        foreach ($catalogos as $catalogo) {
            $result[$catalogo->id] = (string) $catalogo->nombre;
        }

        return $result;
    }

    /** @return array<int, string> */
    public function opcionesProductos(): array
    {
        $productos = Producto::orderBy('nombre')->get();

        $result = [];
        foreach ($productos as $producto) {
            $result[$producto->id] = (string) $producto->nombre;
        }

        return $result;
    }

    /** @param  list<int|string>  $codigos
     * @return array<int, string>
     */
    public function opcionesPorVariosTypes(array $codigos): array
    {
        $catalogos = Catalogo::whereHas(
            'catalogoTipo',
            fn ($q) => $q->whereIn('codigo', $codigos)
        )->get();

        $result = [];
        foreach ($catalogos as $catalogo) {
            $result[$catalogo->id] = (string) $catalogo->nombre;
        }

        return $result;
    }
}
