<?php

namespace App\Filament\Resources\Inventario\MovimientoStock\Widgets;

use App\UseCases\Inventario\Queries\Gestion\ObtenerRotacionInventario;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

class RotacionInventarioChart extends ChartWidget
{
    protected ?string $heading = 'Top 10 Productos con Mayor Rotación (3 meses)';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '250px';

    public static function shouldRegister(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        /** @var Collection<int, object> $filas */
        $filas = app(ObtenerRotacionInventario::class)->ejecutar(['meses' => 3]);

        // Top 10 por índice de rotación
        $top = $filas->sortByDesc('indice_rotacion')->take(10);

        return [
            'datasets' => [
                [
                    'label' => 'Índice de Rotación',
                    'data' => $top->pluck('indice_rotacion')->toArray(),
                    'backgroundColor' => '#0284c7', // Azul
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $top->pluck('producto')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Hace que el gráfico de barras sea horizontal
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
