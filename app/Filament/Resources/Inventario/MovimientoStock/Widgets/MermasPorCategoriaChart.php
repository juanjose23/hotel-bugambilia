<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\MovimientoStock\Widgets;

use App\BusinessLogic\Inventario\Data\Mermas\MermaDetalleData;
use App\Repository\Queries\Inventario\Mermas\ObtenerMermasTotales;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MermasPorCategoriaChart extends ChartWidget
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

    protected ?string $heading = 'Impacto de Mermas por Categoría ($)';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '250px';

    public static function shouldRegister(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        $obtenerMermasTotales = app(ObtenerMermasTotales::class);

        $filas = $obtenerMermasTotales->ejecutar([
            'periodo_desde' => Carbon::now()->startOfMonth(),
            'periodo_hasta' => Carbon::now(),
        ]);

        $agrupado = $filas->groupBy(fn (MermaDetalleData $f): string => $f->categoria)
            ->map(fn ($grupo) => $grupo->sum('perdidaTotal'))
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
