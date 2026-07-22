<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos\Widgets;

use App\Repository\Queries\Activos\ObtenerMantenimientosVencidosUseCase;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MantenimientosVencidosWidget extends BaseWidget
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

    protected ObtenerMantenimientosVencidosUseCase $mantenimientosVencidos;

    public function boot(ObtenerMantenimientosVencidosUseCase $mantenimientosVencidos): void
    {
        $this->mantenimientosVencidos = $mantenimientosVencidos;
    }

    protected function getStats(): array
    {
        $programadosVencidos = $this->mantenimientosVencidos->obtenerProgramadosVencidos()->count();
        $enProcesoSobrepasados = $this->mantenimientosVencidos->obtenerEnProcesoSobrepasados()->count();

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
