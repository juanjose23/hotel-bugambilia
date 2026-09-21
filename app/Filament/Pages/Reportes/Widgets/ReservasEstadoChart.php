<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Reportes\InteligenciaNegocioDashboardQuery;
use Filament\Widgets\ChartWidget;

final class ReservasEstadoChart extends ChartWidget
{
    use UsaRangoFechasDashboard;

    protected ?string $heading = 'Reservas por estado';

    protected ?string $description = 'Distribución por estado.';

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
        $dashboard = $this->dashboard();
        $series = is_array($dashboard['series'] ?? null) ? $dashboard['series'] : [];
        $reservasPorEstado = is_array($series['reservas_por_estado'] ?? null) ? $series['reservas_por_estado'] : [];
        $data = collect($reservasPorEstado);

        return [
            'datasets' => [
                [
                    'label' => 'Reservas',
                    'data' => $data->pluck('total')->map(fn (mixed $value): int => is_numeric($value) ? (int) $value : 0)->values()->all(),
                    'backgroundColor' => [
                        '#F59E0B',
                        '#0EA5E9',
                        '#6b003e',
                        '#16A34A',
                        '#64748B',
                        '#DC2626',
                    ],
                    'borderColor' => 'rgba(255, 255, 255, 0.35)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data->pluck('estado')->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '62%',
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
