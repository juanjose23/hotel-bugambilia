<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Pages;

use App\Filament\Resources\Catalogos\Catalogos\CatalogoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewCatalogo extends ViewRecord
{
    protected static string $resource = CatalogoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->modalHeading('Editar catálogo')
                ->modalWidth(Width::FourExtraLarge),
        ];
    }
}
