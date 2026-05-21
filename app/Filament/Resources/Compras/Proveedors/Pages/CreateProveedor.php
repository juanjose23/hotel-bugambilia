<?php

namespace App\Filament\Resources\Compras\Proveedors\Pages;

use App\Filament\Resources\Compras\Proveedors\ProveedorResource;
use App\UseCases\Compras\Proveedores\Mutations\CrearProveedor;
use App\UseCases\Compras\Proveedores\Queries\GenerarCodigoProveedor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProveedor extends CreateRecord
{
    protected static string $resource = ProveedorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['codigo'] ?? null)) {
            $data['codigo'] = app(GenerarCodigoProveedor::class)->ejecutar();
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(CrearProveedor::class)->execute($data);
    }
}
