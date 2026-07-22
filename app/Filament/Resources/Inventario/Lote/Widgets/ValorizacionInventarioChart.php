<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Widgets;

use App\BusinessLogic\Inventario\Data\Stock\ValorizacionCategoriaData;
use App\Repository\Queries\Inventario\Stock\ObtenerValorizacionInventario;
use App\Repository\Queries\Shared\ObtenerMonedaBase;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class ValorizacionInventarioChart extends ChartWidget
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

    protected static ?int $sort = 2;

    protected ?string $maxHeight = '250px';

    public function getHeading(): ?string
    {
        $simbolo = app(ObtenerMonedaBase::class)->ejecutar()->simbolo ?? 'C$';

        return "Valorización por Categoría ($simbolo)";
    }

    public static function shouldRegister(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        $obtenerValorizacion = app(ObtenerValorizacionInventario::class);
        $filas = $obtenerValorizacion->ejecutar();

        $agrupado = $filas->groupBy(fn (ValorizacionCategoriaData $f): string => $f->categoria ?? 'Sin Categoría')
            ->map(fn ($grupo) => $grupo->sum('valorTotal'))
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
