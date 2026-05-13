<?php

namespace App\Filament\Resources\Compras\OrdenesCompra\Pages;

use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Models\Compras\OrdenCompra;
use Filament\Resources\Pages\CreateRecord;

class CreateOrdenCompra extends CreateRecord
{
    protected static string $resource = OrdenCompraResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $year = now()->year;
        $count = OrdenCompra::whereYear('fecha_orden', $year)->count() + 1;
        $data['codigo'] = "OC-{$year}-".str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        return $data;
    }
}
