<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Pages;

use App\Filament\Resources\Catalogos\Catalogos\CatalogoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCatalogo extends EditRecord
{
    protected static string $resource = CatalogoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
