<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCotizacion extends EditRecord
{
    protected static string $resource = CotizacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
