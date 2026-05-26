<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos\Widgets;

use App\UseCases\Activos\Queries\ObtenerEstadisticasReportesUseCase;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EstadisticasActivosWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $estadisticas = app(ObtenerEstadisticasReportesUseCase::class)->ejecutar();

        return [
            Stat::make('Total de Activos', $estadisticas['totalActivos'])
                ->description('Activos registrados en el sistema')
                ->descriptionIcon(Heroicon::Cube)
                ->color('primary'),

            Stat::make('En Mantenimiento', $estadisticas['enMantenimiento'])
                ->description($estadisticas['mantenimientosVencidos'].' mantenimientos vencidos')
                ->descriptionIcon(Heroicon::WrenchScrewdriver)
                ->color($estadisticas['mantenimientosVencidos'] > 0 ? 'danger' : 'warning'),

            Stat::make('Problemas y Bajas', $estadisticas['extraviados'] + $estadisticas['totalBajas'])
                ->description("{$estadisticas['extraviados']} extraviados, {$estadisticas['totalBajas']} bajas")
                ->descriptionIcon(Heroicon::ExclamationTriangle)
                ->color('danger'),
        ];
    }
}
