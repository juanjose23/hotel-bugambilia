<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Repository\Queries\Reportes\InteligenciaNegocioDashboardQuery;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class AlertasOperacionWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Alertas operativas';

    protected ?string $description = 'Puntos de atención diaria para recepción, limpieza e inventario.';

    public ?string $fechaInicio = null;

    public ?string $fechaFin = null;

    public static function canView(): bool
    {
        return KpisInteligenciaNegocioWidget::canView();
    }

    protected function getStats(): array
    {
        $data = $this->dashboard();
        /** @var array<string, int> $operacion */
        $operacion = is_array($data['operacion'] ?? null) ? $data['operacion'] : [];

        $checkInHoy = (int) ($operacion['check_in_hoy'] ?? 0);
        $checkOutHoy = (int) ($operacion['check_out_hoy'] ?? 0);
        $limpiezasPendientes = (int) ($operacion['limpiezas_pendientes'] ?? 0);
        $stockBajo = (int) ($operacion['stock_bajo'] ?? 0);
        $lotesProximosVencer = (int) ($operacion['lotes_proximos_vencer'] ?? 0);

        return [
            Stat::make('Check-in hoy', $checkInHoy)
                ->description('Llegadas programadas')
                ->descriptionIcon(Heroicon::ArrowRightOnRectangle)
                ->color('info'),

            Stat::make('Check-out hoy', $checkOutHoy)
                ->description('Salidas programadas')
                ->descriptionIcon(Heroicon::ArrowLeftOnRectangle)
                ->color('gray'),

            Stat::make('Limpiezas pendientes', $limpiezasPendientes)
                ->description('Tareas abiertas del día')
                ->descriptionIcon(Heroicon::QueueList)
                ->color($limpiezasPendientes > 0 ? 'warning' : 'success'),

            Stat::make('Stock agotado', $stockBajo)
                ->description($lotesProximosVencer.' lotes próximos a vencer')
                ->descriptionIcon(Heroicon::BellAlert)
                ->color($stockBajo > 0 ? 'danger' : 'success'),
        ];
    }

    /** @return array<string, mixed> */
    private function dashboard(): array
    {
        return app(InteligenciaNegocioDashboardQuery::class)->paraRango(
            $this->fechaInicio ?? now()->startOfMonth()->format('Y-m-d'),
            $this->fechaFin ?? now()->format('Y-m-d'),
        );
    }
}
