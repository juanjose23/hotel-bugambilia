<?php

namespace App\Filament\Resources\Catalogos\Ubicacions\Pages;

use App\Filament\Resources\Catalogos\Ubicacions\UbicacionResource;
use App\Models\Catalogos\Ubicacion;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class ArbolUbicacion extends Page
{
    protected static string $resource = UbicacionResource::class;

    protected string $view = 'filament.resources.catalogos.ubicacions.pages.arbol-ubicacion';

    protected static ?string $title = 'Árbol de Ubicaciones';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTreeData(): array
    {
        /** @var Collection<int, Ubicacion> $allUbications */
        $allUbications = Ubicacion::with(['padre'])
            ->orderBy('orden')
            ->get();

        return $this->buildTree($allUbications);
    }

    /**
     * @param  Collection<int, Ubicacion>  $elements
     * @return array<int, array<string, mixed>>
     */
    protected function buildTree(Collection $elements, int|string|null $parentId = null): array
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
