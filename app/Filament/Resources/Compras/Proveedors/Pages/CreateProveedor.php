<?php

namespace App\Filament\Resources\Compras\Proveedors\Pages;

use App\Filament\Resources\Compras\Proveedors\ProveedorResource;
use App\Interactors\Compras\Proveedores\CrearProveedor;
use App\Interactors\Compras\Proveedores\GenerarCodigoProveedor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProveedor extends CreateRecord
{
    protected GenerarCodigoProveedor $generarCodigoProveedor;

    protected CrearProveedor $crearProveedor;

    public function boot(GenerarCodigoProveedor $generarCodigoProveedor, CrearProveedor $crearProveedor): void
    {
        $this->generarCodigoProveedor = $generarCodigoProveedor;
        $this->crearProveedor = $crearProveedor;
    }

    protected static string $resource = ProveedorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['codigo'] ?? null)) {
            $data['codigo'] = $this->generarCodigoProveedor->ejecutar();
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return $this->crearProveedor->ejecutar($data);
    }
}
