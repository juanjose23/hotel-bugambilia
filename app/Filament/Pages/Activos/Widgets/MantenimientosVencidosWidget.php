<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos\Widgets;

use App\Enums\Activos\EstadoMantenimiento;
use App\Models\Activos\ActivoMantenimiento;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MantenimientosVencidosWidget extends BaseWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $ayer = now()->subDay()->toDateString();

        $programadosVencidos = ActivoMantenimiento::query()
            ->where('estado', EstadoMantenimiento::Programado)
            ->where('fecha_programada', '<=', $ayer)
            ->count();

        $enProcesoSobrepasados = ActivoMantenimiento::query()
            ->where('estado', EstadoMantenimiento::EnProceso)
            ->where('fecha_programada', '<=', now()->subDays(15)->toDateString())
            ->count();

        return [
            Stat::make('Programados Vencidos', $programadosVencidos)
                ->description('No se han iniciado aún')
                ->descriptionIcon(Heroicon::Clock)
                ->color($programadosVencidos > 0 ? 'danger' : 'success'),

            Stat::make('En Proceso Prolongados', $enProcesoSobrepasados)
                ->description('Más de 15 días en curso')
                ->descriptionIcon(Heroicon::ExclamationTriangle)
                ->color($enProcesoSobrepasados > 0 ? 'danger' : 'success'),
        ];
    }
}
