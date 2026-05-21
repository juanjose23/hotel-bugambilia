<?php

namespace App\Filament\Resources\Inventario\Lote\Widgets;

use App\Models\General\Moneda;
use App\UseCases\Inventario\Queries\Stock\ObtenerValorizacionInventario;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use stdClass;

class ValorizacionInventarioChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $maxHeight = '250px';

    public function getHeading(): ?string
    {
        $monedaBase = Moneda::where('es_predeterminada', true)->first();
        $simbolo = $monedaBase ? $monedaBase->simbolo : 'C$';

        return "Valorización por Categoría ({$simbolo})";
    }

    public static function shouldRegister(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        /** @var Collection<int, stdClass> $filas */
        $filas = app(ObtenerValorizacionInventario::class)->ejecutar();

        /** @var Collection<string, float> $agrupado */
        $agrupado = $filas->groupBy(fn (stdClass $f): string => (string) ($f->categoria ?? 'Sin Categoría'))
            ->map(fn ($grupo) => $grupo->sum('valor_total'))
            ->sortDesc();

        return [
            'datasets' => [
                [
                    'label' => 'Valor Total',
                    'data' => $agrupado->values()->toArray(),
                    'backgroundColor' => [
                        '#711C37', '#0369a1', '#15803d', '#b45309', '#be185d',
                        '#4338ca', '#0f766e', '#6d28d9', '#b91c1c', '#047857',
                    ],
                ],
            ],
            'labels' => $agrupado->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
