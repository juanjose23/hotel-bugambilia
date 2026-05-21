<?php

namespace App\Filament\Resources\Compras\OrdenesCompra\Pages;

use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
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
                ->form([
                    DatePicker::make('fecha_inicio')
                        ->label('Fecha inicio')
                        ->default(now()->startOfMonth()),
                    DatePicker::make('fecha_fin')
                        ->label('Fecha fin')
                        ->default(now()),
                ])
                ->action(function (array $data) {
                    $params = [];
                    if ($data['fecha_inicio'] ?? null) {
                        $params['fecha_inicio'] = $data['fecha_inicio'];
                    }
                    if ($data['fecha_fin'] ?? null) {
                        $params['fecha_fin'] = $data['fecha_fin'];
                    }

                    $url = route('reporte.compras.departamentos', $params);

                    return redirect()->away($url);
                })
                ->visible(fn () => auth()->user()->can('Compras:ImprimirReportesCompras')),

            CreateAction::make()
                ->label('Nueva Orden'),
        ];
    }
}
