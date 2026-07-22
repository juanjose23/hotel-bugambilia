<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos\Widgets;

use App\Repository\Queries\Activos\ObtenerEstadisticasReportesUseCase;
use App\Repository\Queries\Shared\ObtenerMonedaBase;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EstadisticasActivosWidget extends BaseWidget
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

    protected ?string $pollingInterval = null;

    protected ObtenerEstadisticasReportesUseCase $estadisticasActivos;

    public function boot(ObtenerEstadisticasReportesUseCase $estadisticasActivos): void
    {
        $this->estadisticasActivos = $estadisticasActivos;
    }

    protected function getStats(): array
    {
        $estadisticas = $this->estadisticasActivos->ejecutar();
        $simbolo = app(ObtenerMonedaBase::class)->ejecutar()->simbolo ?? 'C$';

        return [
            Stat::make('Total de Activos', $estadisticas['totalActivos'])
                ->description('Activos registrados en el sistema')
                ->descriptionIcon(Heroicon::Cube)
                ->color('primary'),

            Stat::make('En Mantenimiento', $estadisticas['enMantenimiento'])
                ->description($estadisticas['mantenimientosVencidos'].' mantenimientos vencidos')
                ->descriptionIcon(Heroicon::WrenchScrewdriver)
                ->color($estadisticas['mantenimientosVencidos'] > 0 ? 'danger' : 'warning'),

            Stat::make('Valor Neto en Libros', $simbolo.' '.number_format((float) ($estadisticas['valorTotalNeto'] ?? 0.0), 2))
                ->description('Valor neto total de activos activos (costo - depreciación)')
                ->descriptionIcon(Heroicon::Banknotes)
                ->color('success'),

            Stat::make('Problemas y Bajas', $estadisticas['extraviados'] + $estadisticas['totalBajas'])
                ->description("{$estadisticas['extraviados']} extraviados, {$estadisticas['totalBajas']} bajas")
                ->descriptionIcon(Heroicon::ExclamationTriangle)
                ->color('danger'),
        ];
    }
}
