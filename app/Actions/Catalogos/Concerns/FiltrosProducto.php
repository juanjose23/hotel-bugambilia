<?php

declare(strict_types=1);

namespace App\Actions\Catalogos\Concerns;

use App\BusinessLogic\Catalogos\Data\ProductoFiltrosData;
use App\Enums\Catalogos\TipoProducto;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use Illuminate\Database\Eloquent\Builder;

trait FiltrosProducto
{
    /**
     * @param  Builder<Producto>  $query
     * @return Builder<Producto>
     */
    protected function aplicarFiltrosQuery(
        Builder $query,
        ProductoFiltrosData $filtros,
    ): Builder {
        if ($filtros->categoriaId !== null) {
            $query->where('categoria_id', $filtros->categoriaId);
        }

        if ($filtros->marcaId !== null) {
            $query->where('marca_id', $filtros->marcaId);
        }

        if ($filtros->tipo !== null) {
            $query->where('tipo', $filtros->tipo);
        }

        if ($filtros->estado !== null) {
            $query->where('estado', $filtros->estado);
        }

        return $query;
    }

    /**
     * @return array<int, array{label: string, valor: string}>
     */
    protected function prepararFiltros(
        ProductoFiltrosData $filtros,
    ): array {
        $categoria = $filtros->categoriaId !== null
            ? Catalogo::find($filtros->categoriaId)?->nombre
            : null;

        $marca = $filtros->marcaId !== null
            ? Catalogo::find($filtros->marcaId)?->nombre
            : null;

        $tipo = $filtros->tipo !== null
            ? TipoProducto::tryFrom($filtros->tipo)?->label()
            : null;

        $estado = $filtros->estado !== null
            ? EstadoGeneral::tryFrom($filtros->estado)?->label()
            : null;

        return [
            [
                'label' => 'Categoría',
                'valor' => $categoria ?? 'TODOS',
            ],
            [
                'label' => 'Marca',
                'valor' => $marca ?? 'TODOS',
            ],
            [
                'label' => 'Tipo',
                'valor' => $tipo ?? 'TODOS',
            ],
            [
                'label' => 'Estado',
                'valor' => $estado ?? 'TODOS',
            ],
        ];
    }
}
