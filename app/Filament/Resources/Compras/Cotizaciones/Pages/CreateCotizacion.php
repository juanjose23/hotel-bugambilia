<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\Events\Compras\CotizacionCreada;
use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Repository\Models\Compras\Cotizacion;
use Filament\Resources\Pages\CreateRecord;

class CreateCotizacion extends CreateRecord
{
    protected static string $resource = CotizacionResource::class;

    protected function afterCreate(): void
    {
        /** @var Cotizacion $record */
        $record = $this->getRecord();
        $record->loadMissing('proveedor', 'items', 'moneda');
        CotizacionCreada::dispatch($record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
