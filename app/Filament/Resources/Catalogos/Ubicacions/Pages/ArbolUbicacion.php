<?php

namespace App\Filament\Resources\Catalogos\Ubicacions\Pages;

use App\Filament\Resources\Catalogos\Ubicacions\UbicacionResource;
use App\UseCases\Catalogos\Queries\ObtenerArbolUbicaciones;
use Filament\Resources\Pages\Page;

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
        return app(ObtenerArbolUbicaciones::class)->execute();
    }
}
