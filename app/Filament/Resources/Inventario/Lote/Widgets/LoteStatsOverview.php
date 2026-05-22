<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Widgets;

use App\Enums\Inventario\EstadoLote;
use App\Models\Inventario\Lote;
use App\Models\Monedas\Moneda;
use App\UseCases\Inventario\Queries\Stock\ObtenerValorizacionInventario;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoteStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $lotesEnStock = Lote::where('estado', EstadoLote::Disponible)
            ->where('cantidad_disponible', '>', 0)
            ->count();

        $lotesCuarentena = Lote::where('estado', EstadoLote::Cuarentena)->count();

        $lotesProximosVencer = Lote::where('estado', EstadoLote::Disponible)
            ->where('cantidad_disponible', '>', 0)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])
            ->count();

        $valorTotal = app(ObtenerValorizacionInventario::class)->totalGeneral();
        $monedaBase = Moneda::where('es_predeterminada', true)->first();
        $simbolo = $monedaBase ? $monedaBase->simbolo : 'C$';

        return [
            Stat::make('Lotes en Stock', (string) $lotesEnStock)
                ->description('Lotes disponibles con stock')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('En Cuarentena', (string) $lotesCuarentena)
                ->description('Lotes retenidos en cuarentena')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color('warning'),

            Stat::make('Próximos a Vencer', (string) $lotesProximosVencer)
                ->description('Lotes venciendo en los próximos 30 días')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('Valor del Inventario', $simbolo.' '.number_format($valorTotal, 2))
                ->description('Valorización del stock disponible')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),
        ];
    }
}
