<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\OrdenesCompra\Pages;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewOrdenCompra extends ViewRecord
{
    protected static string $resource = OrdenCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('imprimir')
                ->label('Imprimir')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->url(fn ($record) => route('admin.compras.reportes.orden-compra', $record))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->can('Compras:ImprimirOrdenCompra') ?? false),

            EditAction::make()
                ->visible(fn ($record) => $record->estado === EstadoOrdenCompra::Borrador),
        ];
    }
}
