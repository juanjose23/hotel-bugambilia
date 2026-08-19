<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Repository\Queries\Reportes\InteligenciaNegocioDashboardQuery;
use Filament\Widgets\ChartWidget;

final class ReservasEstadoChart extends ChartWidget
{
    protected ?string $heading = 'Reservas por estado';

    protected ?string $description = 'Distribución por estado.';

    protected string $color = 'info';

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
                ],
            ],
            'labels' => $data->pluck('estado')->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
