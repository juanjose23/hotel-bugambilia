<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Pages;

use App\Filament\Resources\Catalogos\Catalogos\CatalogoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use App\UseCases\Catalogo\Queries\ObtenerCatalogo;

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

    protected function resolveRecord(int|string $record): \Illuminate\Database\Eloquent\Model
    {
        return app(ObtenerCatalogo::class)->execute(['id' => $record]);
    }
}
