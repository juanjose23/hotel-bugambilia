<?php

namespace App\Filament\Resources\Compras\OrdenesCompra\Pages;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Repository\Models\Compras\OrdenCompra;
use Filament\Resources\Pages\EditRecord;

class EditOrdenCompra extends EditRecord
{
    protected static string $resource = OrdenCompraResource::class;

    protected function canEdit(OrdenCompra $record): bool
    {
        return $record->estado !== EstadoOrdenCompra::Recibida;
    }
}
