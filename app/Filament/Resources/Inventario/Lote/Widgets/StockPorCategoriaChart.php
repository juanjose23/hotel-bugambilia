<?php

namespace App\Filament\Resources\Inventario\Lote\Widgets;

use App\UseCases\Inventario\Queries\Stock\ObtenerStockPorProducto;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use stdClass;

class StockPorCategoriaChart extends ChartWidget
{
    protected ?string $heading = 'Stock Disponible por Categoría';

    protected static ?int $sort = 1;

    protected ?string $maxHeight = '250px';

    public static function shouldRegister(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        /** @var Collection<int, stdClass> $filas */
        $filas = app(ObtenerStockPorProducto::class)->ejecutar();

        /** @var Collection<string, float> $agrupado */
        $agrupado = $filas->groupBy(fn (stdClass $f): string => (string) ($f->categoria ?? 'Sin Categoría'))
            ->map(fn ($grupo) => $grupo->sum('stock_disponible'))
            ->sortDesc()
            ->take(10); // Top 10 categorías

        return [
            'datasets' => [
                [
                    'label' => 'Stock Disponible',
                    'data' => $agrupado->values()->toArray(),
                    'backgroundColor' => '#10b981', // Verde
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $agrupado->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
