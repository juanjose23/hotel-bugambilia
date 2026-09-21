<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\VentaResource\Pages;

use App\Enums\Cuentas\EstadoVenta;
use App\Enums\Facturacion\EstadoFactura;
use App\Filament\Resources\Cuentas\VentaResource\VentaResource;
use App\Filament\Resources\Cuentas\VentaResource\Widgets\VentaStatsOverview;
use App\Repository\Models\Cuentas\Venta;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

final class ListVentas extends ListRecords
{
    protected static string $resource = VentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva Venta Directa')
                ->icon(Heroicon::Plus),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            VentaStatsOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'todas' => Tab::make('Todas')
                ->badge(Venta::query()->count()),

            'emitidas' => Tab::make('Emitidas')
                ->badge(Venta::query()->where('estado', EstadoVenta::Emitida)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('estado', EstadoVenta::Emitida)),

            'con_factura' => Tab::make('Facturadas DGI')
                ->badge(Venta::query()->whereHas('facturas', fn (Builder $q): Builder => $q->where('estado', EstadoFactura::Emitida))->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereHas('facturas', fn (Builder $q): Builder => $q->where('estado', EstadoFactura::Emitida))),

            'sin_factura' => Tab::make('Pendientes DGI')
                ->badge(Venta::query()->where('estado', EstadoVenta::Emitida)->whereDoesntHave('facturas', fn (Builder $q): Builder => $q->where('estado', EstadoFactura::Emitida))->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('estado', EstadoVenta::Emitida)->whereDoesntHave('facturas', fn (Builder $q): Builder => $q->where('estado', EstadoFactura::Emitida))),

            'anuladas' => Tab::make('Anuladas')
                ->badge(Venta::query()->where('estado', EstadoVenta::Anulada)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('estado', EstadoVenta::Anulada)),
        ];
    }
}
