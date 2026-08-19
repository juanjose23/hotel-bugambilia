<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Repository\Queries\Reportes\InteligenciaNegocioDashboardQuery;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

final class TendenciaTemporadaChart extends ChartWidget
{
    protected ?string $heading = 'Tendencia por temporada';

    protected ?string $description = 'Ingresos y reservas por mes.';

    protected string $color = 'primary';

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '180px';

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public ?string $fechaInicio = null;

    public ?string $fechaFin = null;

    public static function canView(): bool
    {
        return KpisInteligenciaNegocioWidget::canView();
    }

    protected function getData(): array
    {
        $dashboard = $this->dashboard();
        $series = is_array($dashboard['series'] ?? null) ? $dashboard['series'] : [];
        $tendenciaTemporada = is_array($series['tendencia_temporada'] ?? null) ? $series['tendencia_temporada'] : [];
        $data = collect($tendenciaTemporada);

        return [
            'datasets' => [
                [
                    'type' => 'bar',
                    'label' => 'Ingresos',
                    'data' => $data->pluck('ingresos')->map(fn (mixed $value): float => is_numeric($value) ? (float) $value : 0.0)->values()->all(),
                    'backgroundColor' => '#6b003e',
                    'borderColor' => '#6b003e',
                    'yAxisID' => 'y',
                ],
                [
                    'type' => 'line',
                    'label' => 'Reservas',
                    'data' => $data->pluck('reservas')->map(fn (mixed $value): int => is_numeric($value) ? (int) $value : 0)->values()->all(),
                    'backgroundColor' => '#0EA5E9',
                    'borderColor' => '#0EA5E9',
                    'tension' => 0.35,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $data
                ->map(function (mixed $item): string {
                    $itemArr = is_array($item) ? $item : [];
                    $periodo = is_scalar($itemArr['periodo'] ?? null) ? (string) $itemArr['periodo'] : '';
                    $temporada = is_scalar($itemArr['temporada'] ?? null) ? (string) $itemArr['temporada'] : '';

                    return Carbon::parse($periodo.'-01')->format('M Y').' · '.$temporada;
                })
                ->values()
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'position' => 'left',
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
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
