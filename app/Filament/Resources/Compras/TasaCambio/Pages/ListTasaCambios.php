<?php

namespace App\Filament\Resources\Compras\TasaCambio\Pages;

use App\Filament\Resources\Catalogos\TasaCambio\TasaCambioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListTasaCambios extends ListRecords
{
    protected static string $resource = TasaCambioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Tasa')
                ->icon(Heroicon::Plus),
        ];
    }
}
