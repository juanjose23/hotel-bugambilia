<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos\Widgets;

use App\BusinessLogic\Activos\CalcularDepreciacionActivo;
use App\Enums\Activos\EstadoActivo;
use App\Repository\Models\Activos\Activo;
use App\Repository\Queries\Shared\ObtenerMonedaBase;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class ValorizacionActivosChart extends ChartWidget
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

    public static function shouldRegister(): bool
    {
        return false;
    }

    public function getHeading(): ?string
    {
        $simbolo = app(ObtenerMonedaBase::class)->ejecutar()->simbolo ?? 'C$';

        return "Valorización de Activos por Categoría ($simbolo)";
    }

    protected function getData(): array
    {
        $activos = Activo::with('producto.categoria')
            ->where('estado', '!=', EstadoActivo::DadoDeBaja->value)
            ->get();

        $calcular = app(CalcularDepreciacionActivo::class);

        $agrupado = $activos->groupBy(fn (Activo $a) => ($a->producto?->categoria)->nombre ?? 'Sin Categoría')
            ->map(function ($grupo) use ($calcular) {
                return $grupo->sum(fn (Activo $a) => $calcular->ejecutar(
                    costoAdquisicion: $a->costo_adquisicion !== null ? (float) $a->costo_adquisicion : null,
                    vidaUtilMeses: $a->vida_util_meses !== null ? (int) $a->vida_util_meses : null,
                    fechaAdquisicion: $a->fecha_adquisicion,
                )['valor_libros'] ?? 0.0);
            })
            ->sortDesc();

        return [
            'datasets' => [
                [
                    'label' => 'Valor Neto',
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
