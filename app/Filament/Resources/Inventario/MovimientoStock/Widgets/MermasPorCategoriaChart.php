<?php

namespace App\Filament\Resources\Inventario\MovimientoStock\Widgets;

use App\UseCases\Inventario\Queries\Mermas\ObtenerMermasTotales;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use stdClass;

class MermasPorCategoriaChart extends ChartWidget
{
    protected ?string $heading = 'Impacto de Mermas por Categoría ($)';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '250px';

    public static function shouldRegister(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        /** @var Collection<int, stdClass> $filas */
        $filas = app(ObtenerMermasTotales::class)->ejecutar([
            'periodo_desde' => now()->startOfMonth(),
            'periodo_hasta' => now(),
        ]);

        /** @var Collection<string, float> $agrupado */
        $agrupado = $filas->groupBy(fn (stdClass $f): string => (string) ($f->categoria ?? 'Sin Categoría'))
            ->map(fn ($grupo) => $grupo->sum('perdida_total'))
            ->sortDesc();

        return [
            'datasets' => [
                [
                    'label' => 'Pérdida ($)',
                    'data' => $agrupado->values()->toArray(),
                    'backgroundColor' => [
                        '#dc2626', '#ea580c', '#d97706', '#ca8a04', '#65a30d',
                        '#059669', '#0891b2', '#2563eb', '#4f46e5', '#7c3aed',
                    ],
                ],
            ],
            'labels' => $agrupado->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'polarArea';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
            ],
        ];
    }
}
