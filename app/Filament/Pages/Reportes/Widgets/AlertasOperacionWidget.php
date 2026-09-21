<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Reportes\InteligenciaNegocioDashboardQuery;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class AlertasOperacionWidget extends StatsOverviewWidget
{
    use UsaRangoFechasDashboard;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Alertas operativas';

    protected ?string $description = 'Puntos de atención diaria para recepción, limpieza e inventario.';

    public static function canView(): bool
    {
        return KpisInteligenciaNegocioWidget::canView();
    }

    protected function getStats(): array
    {
        $data = $this->dashboard();
        /** @var array<string, int> $operacion */
        $operacion = is_array($data['operacion'] ?? null) ? $data['operacion'] : [];

        $habitacionesOcupadasHoy = (int) ($operacion['habitaciones_ocupadas_hoy'] ?? 0);
        $habitacionesTotales = (int) ($operacion['habitaciones_totales'] ?? 0);
        $checkInHoy = (int) ($operacion['check_in_hoy'] ?? 0);
        $checkOutHoy = (int) ($operacion['check_out_hoy'] ?? 0);
        $limpiezasPendientes = (int) ($operacion['limpiezas_pendientes'] ?? 0);
        $limpiezasProgramadas = (int) ($operacion['limpiezas_programadas'] ?? 0);
        $stockBajo = (int) ($operacion['stock_bajo'] ?? 0);
        $lotesProximosVencer = (int) ($operacion['lotes_proximos_vencer'] ?? 0);

        $limpiezaDescripcion = $limpiezasPendientes > 0
            ? $limpiezasPendientes.' de '.$limpiezasProgramadas.' tareas pendientes'
            : 'Ninguna tarea pendiente hoy';

        return [
            Stat::make('Habitaciones ocupadas hoy', $habitacionesOcupadasHoy.' / '.$habitacionesTotales)
                ->description('Ocupación en tiempo real')
                ->descriptionIcon(Heroicon::BuildingOffice)
                ->color('primary'),

            Stat::make('Check-in hoy', $checkInHoy)
                ->description('Llegadas programadas')
                ->descriptionIcon(Heroicon::ArrowRightOnRectangle)
                ->color('info'),

            Stat::make('Limpiezas pendientes', $limpiezasPendientes)
                ->description($limpiezaDescripcion)
                ->descriptionIcon(Heroicon::QueueList)
                ->color($limpiezasPendientes > 0 ? 'warning' : 'success'),

            Stat::make('Stock agotado', $stockBajo)
                ->description('Check-out hoy: '.$checkOutHoy.' · '.$lotesProximosVencer.' lotes por vencer')
                ->descriptionIcon(Heroicon::BellAlert)
                ->color($stockBajo > 0 ? 'danger' : 'success'),
        ];
    }

    /** @return array<string, mixed> */
    private function dashboard(): array
    {
        $rango = $this->rangoDashboard();

        return app(InteligenciaNegocioDashboardQuery::class)->paraRango(
            $rango['inicio'],
            $rango['fin'],
        );
    }
}
