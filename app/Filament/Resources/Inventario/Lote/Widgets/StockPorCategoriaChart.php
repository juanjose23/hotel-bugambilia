<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Widgets;

use App\BusinessLogic\Inventario\Data\Stock\StockProductoData;
use App\Repository\Queries\Inventario\Stock\ObtenerStockPorProducto;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class StockPorCategoriaChart extends ChartWidget
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

    protected ?string $heading = 'Stock Disponible por Categoría';

    protected static ?int $sort = 1;

    protected ?string $maxHeight = '250px';

    public static function shouldRegister(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        $obtenerStockPorProducto = app(ObtenerStockPorProducto::class);

        $filas = $obtenerStockPorProducto->ejecutar();

        $agrupado = $filas->groupBy(fn (StockProductoData $f): string => $f->categoria ?? 'Sin Categoría')
            ->map(fn ($grupo) => $grupo->sum('stockDisponible'))
            ->sortDesc()
            ->take(10);

        return [
            'datasets' => [
                [
                    'label' => 'Stock Disponible',
                    'data' => $agrupado->values()->toArray(),
                    'backgroundColor' => '#10b981',
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
