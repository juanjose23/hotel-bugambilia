<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\MovimientoStock\Widgets;

use App\Repository\Queries\Inventario\Gestion\ObtenerRotacionInventario;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class RotacionInventarioChart extends ChartWidget
{
    use HasWidgetShield;

    public static function canView(): bool
    {
        $permission = static::getWidgetPermission();
        $user = auth()->user();

        return $permission && $user
            ? $user->can($permission)
            : parent::canView();
    }

    protected ?string $heading = 'Top 10 Productos con Mayor Rotación (3 meses)';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '250px';

    public static function shouldRegister(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        $obtenerRotacionInventario = app(ObtenerRotacionInventario::class);

        $filas = $obtenerRotacionInventario->ejecutar(['meses' => 3]);

        $top = $filas->sortByDesc('indiceRotacion')->take(10);

        return [
            'datasets' => [
                [
                    'label' => 'Índice de Rotación',
                    'data' => $top->pluck('indiceRotacion')->toArray(),
                    'backgroundColor' => '#0284c7',
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
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
