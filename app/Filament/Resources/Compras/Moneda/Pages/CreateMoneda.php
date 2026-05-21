<?php

namespace App\Filament\Resources\Compras\Moneda\Pages;

use App\Filament\Resources\Catalogos\Moneda\MonedaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMoneda extends CreateRecord
{
    protected static string $resource = MonedaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
