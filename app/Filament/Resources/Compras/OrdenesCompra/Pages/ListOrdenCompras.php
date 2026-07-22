<?php

namespace App\Filament\Resources\Compras\OrdenesCompra\Pages;

use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrdenCompras extends ListRecords
{
    protected static string $resource = OrdenCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [

            CreateAction::make()
                ->label('Nueva Orden'),
        ];
    }
}
