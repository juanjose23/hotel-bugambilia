<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Reportes\InteligenciaNegocioDashboardQuery;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

final class IngresosReservasChart extends ChartWidget
{
    use UsaRangoFechasDashboard;

    protected ?string $heading = 'Tendencia de ingresos por reservas';

    protected ?string $description = 'Evolución diaria del período.';

    protected string $color = 'success';

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '180px';

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        return KpisInteligenciaNegocioWidget::canView();
    }

    protected function getData(): array
    {
        $dashboard = $this->dashboard();
        $series = is_array($dashboard['series'] ?? null) ? $dashboard['series'] : [];
        $ingresosPorDia = is_array($series['ingresos_por_dia'] ?? null) ? $series['ingresos_por_dia'] : [];
        $data = collect($ingresosPorDia);

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $data->pluck('total')->map(fn (mixed $value): float => is_numeric($value) ? (float) $value : 0.0)->values()->all(),
                    'backgroundColor' => 'rgba(22, 163, 74, 0.15)',
                    'borderColor' => '#16A34A',
                    'borderWidth' => 2,
                    'pointRadius' => 2,
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('fecha')
                ->map(fn (mixed $fecha): string => Carbon::parse(is_scalar($fecha) ? (string) $fecha : '')->format('d/m'))
                ->values()
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
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
