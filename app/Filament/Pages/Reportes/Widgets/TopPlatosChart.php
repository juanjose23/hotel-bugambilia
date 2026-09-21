<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Restaurante\Reportes\ObtenerMetricasRestauranteQuery;
use Filament\Widgets\ChartWidget;

final class TopPlatosChart extends ChartWidget
{
    use UsaRangoFechasDashboard;

    protected ?string $heading = 'Top platos del período';

    protected ?string $description = 'Ingresos por plato (items no anulados).';

    protected string $color = 'warning';

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
        $topPlatos = is_array($data['top_platos'] ?? null) ? $data['top_platos'] : [];
        $collection = collect($topPlatos);

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $collection->pluck('ingresos')->map(fn (mixed $value): float => is_numeric($value) ? (float) $value : 0.0)->values()->all(),
                    'backgroundColor' => '#F59E0B',
                    'borderColor' => 'rgba(255, 255, 255, 0.35)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $collection->pluck('plato')->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
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
