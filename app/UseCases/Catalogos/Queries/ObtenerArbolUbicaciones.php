<?php

namespace App\UseCases\Catalogos\Queries;

use App\Models\Catalogos\Ubicacion;
use Illuminate\Support\Collection;

class ObtenerArbolUbicaciones
{
    /** @return array<int, array<string, mixed>> */
    public function execute(): array
    {
        $all = Ubicacion::with(['padre'])
            ->orderBy('orden')
            ->get();

        return $this->buildTree($all);
    }

    /**
     * @param  Collection<int, Ubicacion>  $elements
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(Collection $elements, int|string|null $parentId = null): array
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element->padre_id == $parentId) {
                $children = $this->buildTree($elements, $element->id);
                $data = $element->toArray();
                $data['children'] = $children;
                $branch[] = $data;
            }
        }

        return $branch;
    }
}
