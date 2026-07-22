<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Widgets;

use App\Repository\Models\Inventario\Lote;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesProximosVencer;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class LotesEnRiesgoChart extends ChartWidget
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

    protected ?string $heading = 'Lotes Próximos a Vencer (Línea de Tiempo)';

    protected static ?int $sort = 5;

    protected ?string $maxHeight = '250px';

    public static function shouldRegister(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        $obtenerLotesProximosVencer = app(ObtenerLotesProximosVencer::class);

        $lotes = $obtenerLotesProximosVencer->ejecutar(['dias' => 30]);

        $menosDe7Dias = $lotes->filter(fn (Lote $l) => now()->diffInDays($l->fecha_vencimiento) <= 7)->count();
        $de8a15Dias = $lotes->filter(fn (Lote $l) => now()->diffInDays($l->fecha_vencimiento) > 7 && now()->diffInDays($l->fecha_vencimiento) <= 15)->count();
        $de16a30Dias = $lotes->filter(fn (Lote $l) => now()->diffInDays($l->fecha_vencimiento) > 15 && now()->diffInDays($l->fecha_vencimiento) <= 30)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad de Lotes',
                    'data' => [$menosDe7Dias, $de8a15Dias, $de16a30Dias],
                    'backgroundColor' => ['#dc2626', '#f59e0b', '#fbbf24'],
                    'borderRadius' => 4,
                ],
            ],
            'labels' => ['Crítico (0-7 días)', 'Precaución (8-15 días)', 'Atención (16-30 días)'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
