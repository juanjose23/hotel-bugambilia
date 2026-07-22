<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos\Widgets;

use App\Repository\Queries\Activos\ObtenerMantenimientosProximosUseCase;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProximosMantenimientosWidget extends BaseWidget
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

    protected ObtenerMantenimientosProximosUseCase $mantenimientosProximos;

    public function boot(ObtenerMantenimientosProximosUseCase $mantenimientosProximos): void
    {
        $this->mantenimientosProximos = $mantenimientosProximos;
    }

    protected function getStats(): array
    {
        $proximos7 = $this->mantenimientosProximos->execute(7)->count();
        $proximos30 = $this->mantenimientosProximos->execute(30)->count();

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
