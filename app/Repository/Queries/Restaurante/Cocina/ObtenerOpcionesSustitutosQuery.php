<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\Repository\Models\Catalogos\ProductoVariante;
use Illuminate\Database\Eloquent\Builder;

final readonly class ObtenerOpcionesSustitutosQuery
{
    /** @return array<int, string> */
    public function buscar(int $originalId, string $busqueda = ''): array
    {
        $q = $this->consulta($originalId);
        $termino = trim(mb_substr($busqueda, 0, 100));
        if ($termino !== '') {
            $q->where(fn (Builder $v) => $v->whereLike('codigo', '%'.$termino.'%')
                ->orWhereLike('nombre_variante', '%'.$termino.'%')
                ->orWhereHas('producto', fn (Builder $p) => $p->whereLike('nombre', '%'.$termino.'%')));
        }

        return $q->orderBy('codigo')->limit(50)->get()->mapWithKeys(fn (ProductoVariante $v): array => [$v->id => $this->nombre($v)])->all();
    }

    public function etiqueta(int $id, int $originalId): ?string
    {
        $v = $this->consulta($originalId)->find($id);

        return $v !== null ? $this->nombre($v) : null;
    }

    /** @return Builder<ProductoVariante> */
    private function consulta(int $originalId): Builder
    {
        return ProductoVariante::query()->with(['producto', 'unidadMedida'])->where('estado', 1)->where('id', '!=', $originalId);
    }

    private function nombre(ProductoVariante $v): string
    {
        $unidad = $v->unidadMedida?->nombre;

        return ($v->producto->nombre ?? 'Producto').' - '.($v->nombre_variante ?: $v->codigo).($unidad !== null ? ' ('.$unidad.')' : '');
    }
}
