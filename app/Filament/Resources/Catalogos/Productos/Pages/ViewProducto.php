<?php

namespace App\Filament\Resources\Catalogos\Productos\Pages;

use App\Filament\Resources\Catalogos\Productos\ProductoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProducto extends ViewRecord
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
