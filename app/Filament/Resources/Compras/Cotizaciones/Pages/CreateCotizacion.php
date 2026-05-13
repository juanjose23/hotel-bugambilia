<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCotizacion extends CreateRecord
{
    protected static string $resource = CotizacionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
