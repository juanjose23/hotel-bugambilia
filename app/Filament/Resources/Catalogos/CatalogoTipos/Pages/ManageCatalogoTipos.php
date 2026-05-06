<?php

namespace App\Filament\Resources\Catalogos\CatalogoTipos\Pages;

use App\Filament\Resources\Catalogos\CatalogoTipos\CatalogoTipoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCatalogoTipos extends ManageRecords
{
    protected static string $resource = CatalogoTipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('4xl'),
        ];
    }
}
