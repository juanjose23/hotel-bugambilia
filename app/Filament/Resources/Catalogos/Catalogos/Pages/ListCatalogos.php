<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Pages;

use App\Filament\Resources\Catalogos\Catalogos\CatalogoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListCatalogos extends ListRecords
{
    protected static string $resource = CatalogoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Crear catálogo')
                ->modalWidth(Width::FourExtraLarge),
        ];
    }
}
