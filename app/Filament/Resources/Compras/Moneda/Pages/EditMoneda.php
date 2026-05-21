<?php

namespace App\Filament\Resources\Compras\Moneda\Pages;

use App\Filament\Resources\Catalogos\Moneda\MonedaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMoneda extends EditRecord
{
    protected static string $resource = MonedaResource::class;

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
