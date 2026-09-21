<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\VentaResource\Widgets;

use App\Repository\Queries\Cuentas\ObtenerEstadisticasVentasQuery;
use App\Support\MonedaHelper;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class VentaStatsOverview extends BaseWidget
{
    use HasWidgetShield;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $stats = app(ObtenerEstadisticasVentasQuery::class)->ejecutar();

        $varVentasSigno = $stats['variacion_ventas_porcentaje'] >= 0 ? '+' : '';
        $varVentasDesc = "{$varVentasSigno}{$stats['variacion_ventas_porcentaje']}% vs mes anterior";
        $varVentasIcon = $stats['variacion_ventas_porcentaje'] >= 0
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down';
        $varVentasColor = $stats['variacion_ventas_porcentaje'] >= 0 ? 'success' : 'danger';

        $varIngresosSigno = $stats['variacion_ingresos_porcentaje'] >= 0 ? '+' : '';
        $varIngresosDesc = "{$varIngresosSigno}{$stats['variacion_ingresos_porcentaje']}% vs mes anterior";
        $varIngresosIcon = $stats['variacion_ingresos_porcentaje'] >= 0
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down';
        $varIngresosColor = $stats['variacion_ingresos_porcentaje'] >= 0 ? 'success' : 'danger';

        return [
            Stat::make('Total Ventas', number_format($stats['total_ventas']))
                ->description('Histórico acumulado')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->chart($stats['sparkline_ventas'])
                ->color('primary'),

            Stat::make('Ventas del Mes', number_format($stats['ventas_mes']))
                ->description($varVentasDesc)
                ->descriptionIcon($varVentasIcon)
                ->chart($stats['sparkline_ventas'])
                ->color($varVentasColor),

            Stat::make('Ticket Promedio', MonedaHelper::formatear($stats['ticket_promedio']))
                ->description('Promedio por orden emitida')
                ->descriptionIcon('heroicon-m-calculator')
                ->chart($stats['sparkline_ticket'])
                ->color('info'),

            Stat::make('Ingresos del Mes', MonedaHelper::formatear($stats['ingresos_mes']))
                ->description($varIngresosDesc)
                ->descriptionIcon($varIngresosIcon)
                ->chart($stats['sparkline_ingresos'])
                ->color($varIngresosColor),
        ];
    }
}
