<?php

namespace App\Filament\Resources\Compras\DevolucionCompra\Pages;

use App\Filament\Resources\Compras\DevolucionCompra\DevolucionCompraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListDevolucionCompras extends ListRecords
{
    protected static string $resource = DevolucionCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Devolución')
                ->icon(Heroicon::Plus),
        ];
    }
}
