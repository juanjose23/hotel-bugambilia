<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Pages;

use App\Filament\Resources\Catalogos\Catalogos\CatalogoResource;
use App\UseCases\Catalogo\Commands\CrearCatalogo;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCatalogos extends ListRecords
{
    protected static string $resource = CatalogoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Crear catálogo')
                ->modalWidth('lg')
                ->using(fn(array $data) => app(CrearCatalogo::class)->execute($data)),
        ];
    }
}
