<?php

namespace App\Filament\Resources\Compras\OrdenesCompra\Pages;

use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListOrdenCompras extends ListRecords
{
    protected static string $resource = OrdenCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reporte_estadistico')
                ->label('Resumen por Departamento')
                ->icon(Heroicon::ChartBar)
                ->color('warning')
                ->url(fn () => route('reporte.compras.departamentos'))
                ->openUrlInNewTab(),

            CreateAction::make()
                ->label('Nueva Orden'),
        ];
    }
}
