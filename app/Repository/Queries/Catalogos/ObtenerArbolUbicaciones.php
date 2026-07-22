<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Repository\Models\Catalogos\Ubicacion;
use Illuminate\Support\Collection;

class ObtenerArbolUbicaciones
{
    /**
     * @return array<int, UbicacionNodo>
     */
    public function ejecutar(): array
    {
        $all = Ubicacion::query()
            ->select(['id', 'nombre', 'tipo', 'padre_id', 'orden'])
            ->orderBy('orden')
            ->get();

        return $this->buildTree($all);
    }

    /**
     * @param  Collection<int, Ubicacion>  $elements
     * @return array<int, UbicacionNodo>
     */
    private function buildTree(Collection $elements, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element->padre_id === $parentId) {
                $children = $this->buildTree($elements, $element->id);
                $branch[] = new UbicacionNodo(
                    id: $element->id,
                    nombre: $element->nombre,
                    tipo: $element->tipo,
                    padreId: $element->padre_id,
                    children: $children,
                );
            }
        }

        return $branch;
    }
}
