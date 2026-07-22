<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\OrdenesCompra\Pages;

use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Interactors\Compras\OrdenesCompra\GenerarCodigoOrdenCompra;
use Filament\Resources\Pages\CreateRecord;

class CreateOrdenCompra extends CreateRecord
{
    protected GenerarCodigoOrdenCompra $generarCodigoOrdenCompra;

    public function boot(GenerarCodigoOrdenCompra $generarCodigoOrdenCompra): void
    {
        $this->generarCodigoOrdenCompra = $generarCodigoOrdenCompra;
    }

    protected static string $resource = OrdenCompraResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['codigo'] = $this->generarCodigoOrdenCompra->ejecutar();

        return $data;
    }
}
