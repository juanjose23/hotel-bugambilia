<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Widgets;

use App\Repository\Queries\Inventario\Lotes\ObtenerEstadisticasLotes;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoteStatsOverview extends BaseWidget
{
    use HasWidgetShield;

    public static function canView(): bool
    {
        $permission = static::getWidgetPermission();
        $user = auth()->user();

        return $permission && $user
            ? $user->can($permission)
            : parent::canView();
    }

    protected ObtenerEstadisticasLotes $obtenerEstadisticasLotes;

    public function booted(): void
    {
        $this->obtenerEstadisticasLotes = app(ObtenerEstadisticasLotes::class);
    }

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = $this->obtenerEstadisticasLotes->execute();

        return [
            Stat::make('Lotes en Stock', (string) $stats['lotes_en_stock'])
                ->description('Lotes disponibles con stock')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('En Cuarentena', (string) $stats['lotes_cuarentena'])
                ->description('Lotes retenidos en cuarentena')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color('warning'),

            Stat::make('Próximos a Vencer', (string) $stats['lotes_proximos_vencer'])
                ->description('Lotes venciendo en los próximos 30 días')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('Valor del Inventario', $stats['simbolo_moneda'].' '.number_format($stats['valor_total'], 2))
                ->description('Valorización del stock disponible')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),
        ];
    }
}
