<?php

namespace App\Filament\Resources\Compras\DevolucionCompra\Pages;

use App\Enums\Compras\EstadoDevolucion;
use App\Filament\Resources\Compras\DevolucionCompra\DevolucionCompraResource;
use App\Models\Compras\DevolucionCompra;
use App\Services\Compras\NotificadorCompras;
use App\UseCases\Compras\Devoluciones\Mutations\GenerarCodigoDevolucion;
use Filament\Resources\Pages\CreateRecord;

class CreateDevolucionCompra extends CreateRecord
{
    protected static string $resource = DevolucionCompraResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['codigo'] = app(GenerarCodigoDevolucion::class)->execute();
        $data['estado'] = EstadoDevolucion::Borrador;

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var DevolucionCompra $record */
        $record = $this->record;
        app(NotificadorCompras::class)->devolucionCreada($record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
