<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Reportes\ObtenerMetricasOperacionQuery;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class ResumenOperacionWidget extends StatsOverviewWidget
{
    use UsaRangoFechasDashboard;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Resumen operativo';

    protected ?string $description = 'Housekeeping y mantenimiento con comparación frente al período anterior.';

    public static function canView(): bool
    {
        return KpisInteligenciaNegocioWidget::canView();
    }

    protected function getStats(): array
    {
        $data = $this->dashboard();
        /** @var array<string, float|int> $kpis */
        $kpis = is_array($data['kpis'] ?? null) ? $data['kpis'] : [];
        /** @var array<string, float|int> $anterior */
        $anterior = is_array($data['anterior'] ?? null) ? $data['anterior'] : [];

        $ejecuciones = (int) ($kpis['ejecuciones'] ?? 0);
        $finalizadas = (int) ($kpis['finalizadas'] ?? 0);
        $pendientes = (int) ($kpis['limpiezas_pendientes'] ?? 0);
        $tiempoPromedio = (int) ($kpis['tiempo_promedio_minutos'] ?? 0);
        $bloqueadas = (int) ($kpis['habitaciones_bloqueadas'] ?? 0);
        $mantosVencidos = (int) ($kpis['mantenimientos_vencidos'] ?? 0);
        $mantosProximos = (int) ($kpis['mantenimientos_proximos'] ?? 0);
        $garantiasProximas = (int) ($kpis['garantias_proximas'] ?? 0);
        $activosEnMantenimiento = (int) ($kpis['activos_en_mantenimiento'] ?? 0);

        $deltaEjecuciones = $this->deltaPct($ejecuciones, $this->anterior($anterior, 'ejecuciones'));
        $deltaPendientes = $this->deltaPct($pendientes, $this->anterior($anterior, 'limpiezas_pendientes'));
        $deltaTiempo = $this->deltaPct($tiempoPromedio, $this->anterior($anterior, 'tiempo_promedio_minutos'));

        $porcentajeCompletadas = $ejecuciones > 0 ? round(($finalizadas / $ejecuciones) * 100, 1) : 0.0;

        return [
            Stat::make('Limpiezas programadas', $ejecuciones)
                ->description($finalizadas.' finalizadas ('.$porcentajeCompletadas.'%) · '.$this->textoDelta($deltaEjecuciones))
                ->descriptionIcon($this->iconoDelta($deltaEjecuciones))
                ->descriptionColor($this->colorDelta($deltaEjecuciones))
                ->color('primary'),

            Stat::make('Limpiezas pendientes', $pendientes)
                ->description($this->textoDelta($deltaPendientes))
                ->descriptionIcon(Heroicon::QueueList)
                ->descriptionColor('gray')
                ->color($pendientes > 0 ? 'warning' : 'success'),

            Stat::make('Tiempo promedio de limpieza', $tiempoPromedio.' min')
                ->description($finalizadas > 0 ? $this->textoDelta($deltaTiempo) : 'sin ejecuciones finalizadas')
                ->descriptionIcon($this->iconoDelta($deltaTiempo))
                ->descriptionColor($this->colorDelta($deltaTiempo))
                ->color('info'),

            Stat::make('Habitaciones bloqueadas', $bloqueadas)
                ->description('Mantenimiento, sucias o inactivas')
                ->descriptionIcon(Heroicon::BuildingOffice)
                ->color($bloqueadas > 0 ? 'danger' : 'success'),

            Stat::make('Mantenimientos vencidos', $mantosVencidos)
                ->description($mantosProximos.' programados en los próximos 7 días')
                ->descriptionIcon(Heroicon::WrenchScrewdriver)
                ->color($mantosVencidos > 0 ? 'danger' : 'success'),

            Stat::make('Activos en mantenimiento', $activosEnMantenimiento)
                ->description('Garantías por vencer: '.$garantiasProximas)
                ->descriptionIcon(Heroicon::WrenchScrewdriver)
                ->color($activosEnMantenimiento > 0 ? 'warning' : 'success'),
        ];
    }

    /**
     * @param  array<string, float|int>  $anterior
     */
    private function anterior(array $anterior, string $key): float
    {
        $valor = $anterior[$key] ?? null;

        return is_numeric($valor) ? (float) $valor : 0.0;
    }

    private function deltaPct(float $actual, float $anterior): ?float
    {
        if (abs($anterior) < 0.005) {
            return $actual > 0 ? null : 0.0;
        }

        return (($actual - $anterior) / abs($anterior)) * 100;
    }

    private function textoDelta(?float $delta): string
    {
        if ($delta === null) {
            return 'sin referencia previa';
        }

        if (abs($delta) < 0.05) {
            return 'igual al período anterior';
        }

        $signo = $delta > 0 ? '+' : '';

        return "vs período anterior {$signo}".number_format($delta, 1).'%';
    }

    private function iconoDelta(?float $delta): Heroicon
    {
        if ($delta === null || abs($delta) < 0.05) {
            return Heroicon::ArrowRightOnRectangle;
        }

        return $delta > 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown;
    }

    private function colorDelta(?float $delta): string
    {
        if ($delta === null || abs($delta) < 0.05) {
            return 'gray';
        }

        return $delta > 0 ? 'success' : 'danger';
    }

    /** @return array<string, mixed> */
    private function dashboard(): array
    {
        $rango = $this->rangoDashboard();

        return app(ObtenerMetricasOperacionQuery::class)->paraRango(
            $rango['inicio'],
            $rango['fin'],
        );
    }
}
