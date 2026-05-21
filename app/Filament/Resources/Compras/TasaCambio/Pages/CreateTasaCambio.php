<?php

namespace App\Filament\Resources\Compras\TasaCambio\Pages;

use App\Filament\Resources\Catalogos\TasaCambio\TasaCambioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTasaCambio extends CreateRecord
{
    protected static string $resource = TasaCambioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
