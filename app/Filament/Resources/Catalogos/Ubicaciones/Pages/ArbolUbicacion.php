<?php

namespace App\Filament\Resources\Catalogos\Ubicaciones\Pages;

use App\Filament\Resources\Catalogos\Ubicaciones\UbicacionResource;
use App\Repository\Queries\Catalogos\ObtenerArbolUbicaciones;
use App\Repository\Queries\Catalogos\UbicacionNodo;
use Filament\Resources\Pages\Page;

class ArbolUbicacion extends Page
{
    protected ObtenerArbolUbicaciones $obtenerArbolUbicaciones;

    public function boot(ObtenerArbolUbicaciones $obtenerArbolUbicaciones): void
    {
        $this->obtenerArbolUbicaciones = $obtenerArbolUbicaciones;
    }

    protected static string $resource = UbicacionResource::class;

    protected string $view = 'filament.resources.catalogos.ubicaciones.pages.arbol-ubicacion';

    protected static ?string $title = 'Árbol de Ubicaciones';

    /**
     * @return array<int, UbicacionNodo>
     */
    public function getTreeData(): array
    {
        return $this->obtenerArbolUbicaciones->ejecutar();
    }
}
