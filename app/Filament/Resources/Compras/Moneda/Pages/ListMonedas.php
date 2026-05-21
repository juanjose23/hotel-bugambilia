<?php

namespace App\Filament\Resources\Compras\Moneda\Pages;

use App\Filament\Resources\Catalogos\Moneda\MonedaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListMonedas extends ListRecords
{
    protected static string $resource = MonedaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Moneda')
                ->icon(Heroicon::Plus),
        ];
    }
}
