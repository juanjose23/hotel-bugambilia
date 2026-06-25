<?php

namespace App\Filament\Resources\Monedas\TasaCambio\Pages;

use App\Filament\Resources\Monedas\TasaCambio\TasaCambioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTasaCambio extends CreateRecord
{
    protected static string $resource = TasaCambioResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
