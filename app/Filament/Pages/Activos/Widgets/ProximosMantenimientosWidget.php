<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos\Widgets;

use App\Enums\Activos\EstadoMantenimiento;
use App\Models\Activos\ActivoMantenimiento;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProximosMantenimientosWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $hoy = now()->toDateString();
        $en7dias = now()->addDays(7)->toDateString();
        $en30dias = now()->addDays(30)->toDateString();

        $proximos7 = ActivoMantenimiento::query()
            ->where('estado', EstadoMantenimiento::Programado)
            ->whereBetween('fecha_programada', [$hoy, $en7dias])
            ->count();

        $proximos30 = ActivoMantenimiento::query()
            ->where('estado', EstadoMantenimiento::Programado)
            ->whereBetween('fecha_programada', [$hoy, $en30dias])
            ->count();

        return [
            Stat::make('Próximos 7 días', $proximos7)
                ->description('Mantenimientos programados para esta semana')
                ->descriptionIcon(Heroicon::CalendarDays)
                ->color($proximos7 > 0 ? 'warning' : 'success'),

            Stat::make('Próximos 30 días', $proximos30)
                ->description('Total programados en el próximo mes')
                ->descriptionIcon(Heroicon::Calendar)
                ->color($proximos30 > 5 ? 'warning' : 'info'),
        ];
    }
}
