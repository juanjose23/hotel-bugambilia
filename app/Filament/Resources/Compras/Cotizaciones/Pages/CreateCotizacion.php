<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Models\Compras\Cotizacion;
use App\Services\Compras\NotificadorCompras;
use Filament\Resources\Pages\CreateRecord;

class CreateCotizacion extends CreateRecord
{
    protected static string $resource = CotizacionResource::class;

    protected function afterCreate(): void
    {
        /** @var Cotizacion $record */
        $record = $this->getRecord();
        app(NotificadorCompras::class)->cotizacionCreada($record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
