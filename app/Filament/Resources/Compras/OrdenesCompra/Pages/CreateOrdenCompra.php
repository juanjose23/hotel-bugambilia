<?php

namespace App\Filament\Resources\Compras\OrdenesCompra\Pages;

use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\UseCases\Compras\OrdenesCompra\Mutations\GenerarCodigoOrdenCompra;
use Filament\Resources\Pages\CreateRecord;

class CreateOrdenCompra extends CreateRecord
{
    protected static string $resource = OrdenCompraResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['codigo'] = app(GenerarCodigoOrdenCompra::class)->execute();

        return $data;
    }
}
