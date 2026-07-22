<?php

namespace App\Filament\Resources\Compras\DevolucionCompra\Pages;

use App\Enums\Compras\EstadoDevolucion;
use App\Events\Compras\DevolucionCreada;
use App\Filament\Resources\Compras\DevolucionCompra\DevolucionCompraResource;
use App\Interactors\Compras\Devoluciones\GenerarCodigoDevolucion;
use App\Repository\Models\Compras\DevolucionCompra;
use Filament\Resources\Pages\CreateRecord;

class CreateDevolucionCompra extends CreateRecord
{
    protected GenerarCodigoDevolucion $generarCodigoDevolucion;

    public function boot(GenerarCodigoDevolucion $generarCodigoDevolucion): void
    {
        $this->generarCodigoDevolucion = $generarCodigoDevolucion;
    }

    protected static string $resource = DevolucionCompraResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['codigo'] = $this->generarCodigoDevolucion->ejecutar();
        $data['estado'] = EstadoDevolucion::Borrador;

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var DevolucionCompra $record */
        $record = $this->record;
        DevolucionCreada::dispatch($record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
