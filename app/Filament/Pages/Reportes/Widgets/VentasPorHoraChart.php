<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Restaurante\Reportes\ObtenerMetricasRestauranteQuery;
use Filament\Widgets\ChartWidget;

final class VentasPorHoraChart extends ChartWidget
{
    use UsaRangoFechasDashboard;

    protected ?string $heading = 'Ventas por hora';

    protected ?string $description = 'Distribución horaria de las ventas operativas.';

    protected string $color = 'info';

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
        $data = $this->dashboard();
        $porHora = is_array($data['ventas_por_hora'] ?? null) ? $data['ventas_por_hora'] : [];
        $collection = collect($porHora);

        return [
            'datasets' => [
                [
                    'label' => 'Ventas',
                    'data' => $collection->pluck('ventas')->map(fn (mixed $value): float => is_numeric($value) ? (float) $value : 0.0)->values()->all(),
                    'backgroundColor' => 'rgba(14, 165, 233, 0.15)',
                    'borderColor' => '#0EA5E9',
                    'borderWidth' => 2,
                    'pointRadius' => 2,
                    'tension' => 0.35,
                    'fill' => true,
                ],
            ],
            'labels' => $collection->pluck('hora')
                ->map(fn (mixed $hora): string => is_scalar($hora) ? (string) $hora.':00' : '')
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

        return app(ObtenerMetricasRestauranteQuery::class)->paraRango(
            $rango['inicio'],
            $rango['fin'],
        );
    }
}
