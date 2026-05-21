<?php

namespace App\Filament\Resources\Compras\TasaCambio\Pages;

use App\Filament\Resources\Catalogos\TasaCambio\TasaCambioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTasaCambio extends EditRecord
{
    protected static string $resource = TasaCambioResource::class;

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
