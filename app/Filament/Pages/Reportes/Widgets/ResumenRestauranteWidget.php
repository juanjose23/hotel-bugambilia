<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Restaurante\Reportes\ObtenerMetricasRestauranteQuery;
use App\Support\MonedaHelper;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class ResumenRestauranteWidget extends StatsOverviewWidget
{
    use UsaRangoFechasDashboard;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Resumen restaurante & domicilios';

    protected ?string $description = 'Ventas operativas, ticket promedio y tiempos de comanda.';

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

        $ventas = (float) ($kpis['ventas'] ?? 0.0);
        $pedidos = (int) ($kpis['pedidos'] ?? 0);
        $ticket = (float) ($kpis['ticket_promedio'] ?? 0.0);
        $tiempo = (int) ($kpis['tiempo_promedio_min'] ?? 0);
        $ventasSalon = (float) ($kpis['ventas_salon'] ?? 0.0);
        $ventasDomicilio = (float) ($kpis['ventas_domicilio'] ?? 0.0);
        $ventasHabitacion = (float) ($kpis['ventas_habitacion'] ?? 0.0);

        $deltaVentas = $this->deltaPct($ventas, $this->anterior($anterior, 'ventas'));
        $deltaTicket = $this->deltaPct($ticket, $this->anterior($anterior, 'ticket_promedio'));

        return [
            Stat::make('Ventas operativas', MonedaHelper::formatear($ventas))
                ->description($pedidos.' comandas · '.$this->textoDelta($deltaVentas))
                ->descriptionIcon($this->iconoDelta($deltaVentas))
                ->descriptionColor($this->colorDelta($deltaVentas))
                ->color('success'),

            Stat::make('Ticket promedio', MonedaHelper::formatear($ticket))
                ->description($this->textoDelta($deltaTicket))
                ->descriptionIcon($this->iconoDelta($deltaTicket))
                ->descriptionColor($this->colorDelta($deltaTicket))
                ->color('primary'),

            Stat::make('Tiempo promedio de comanda', $tiempo.' min')
                ->description('Apertura a cierre operativo')
                ->descriptionIcon(Heroicon::Clock)
                ->color('info'),

            Stat::make('Ventas por tipo', MonedaHelper::formatear($ventasSalon))
                ->description('Salón · Dom: '.MonedaHelper::formatear($ventasDomicilio).' · Hab: '.MonedaHelper::formatear($ventasHabitacion))
                ->descriptionIcon(Heroicon::BuildingStorefront)
                ->color('warning'),
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

        return app(ObtenerMetricasRestauranteQuery::class)->paraRango(
            $rango['inicio'],
            $rango['fin'],
        );
    }
}
