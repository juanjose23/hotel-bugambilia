<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes\Widgets;

use App\Filament\Pages\Reportes\Widgets\Concerns\UsaRangoFechasDashboard;
use App\Repository\Queries\Reportes\InteligenciaNegocioDashboardQuery;
use App\Support\MonedaHelper;
use Carbon\CarbonImmutable;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class KpisInteligenciaNegocioWidget extends StatsOverviewWidget
{
    use UsaRangoFechasDashboard;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Resumen ejecutivo';

    public function getDescription(): string
    {
        $rango = $this->rangoDashboard();
        $dias = max(1, (int) CarbonImmutable::parse($rango['inicio'])->diffInDays(CarbonImmutable::parse($rango['fin'])) + 1);
        $fInicio = CarbonImmutable::parse($rango['inicio'])->format('d/m/Y');
        $fFin = CarbonImmutable::parse($rango['fin'])->format('d/m/Y');

        return "Período activo: {$fInicio} al {$fFin} ({$dias} días) · Comparación automática con el período anterior equivalente.";
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->can('Page:TableroInteligenciaNegocio') === true
            || $user?->can('Reportes:InteligenciaNegocio') === true;
    }

    protected function getStats(): array
    {
        $data = $this->dashboard();
        /** @var array<string, int|float> $kpis */
        $kpis = is_array($data['kpis'] ?? null) ? $data['kpis'] : [];
        /** @var array<string, int|float> $anterior */
        $anterior = is_array($data['anterior'] ?? null) ? $data['anterior'] : [];
        /** @var array<string, mixed> $series */
        $series = is_array($data['series'] ?? null) ? $data['series'] : [];

        $ingresos = (float) ($kpis['ingresos_reservas'] ?? 0.0);
        $reservas = (int) ($kpis['reservas'] ?? 0);
        $ocupacion = (float) ($kpis['ocupacion'] ?? 0.0);
        $adr = (float) ($kpis['adr'] ?? 0.0);
        $revpar = (float) ($kpis['revpar'] ?? 0.0);
        $cobrado = (float) ($kpis['cobrado'] ?? 0.0);
        $facturado = (float) ($kpis['facturado'] ?? 0.0);
        $restaurante = (float) ($kpis['restaurante'] ?? 0.0);
        $cuentasPorCobrar = (float) ($kpis['cuentas_por_cobrar'] ?? 0.0);

        $deltaIngresos = $this->deltaPct($ingresos, $this->anterior($anterior, 'ingresos_reservas'));
        $deltaOcupacion = $this->deltaPuntos($ocupacion, $this->anterior($anterior, 'ocupacion'));
        $deltaCobrado = $this->deltaPct($cobrado, $this->anterior($anterior, 'cobrado'));
        $deltaFacturado = $this->deltaPct($facturado, $this->anterior($anterior, 'facturado'));
        $deltaRestaurante = $this->deltaPct($restaurante, $this->anterior($anterior, 'restaurante'));
        $deltaCuentasPorCobrar = $this->deltaPct($cuentasPorCobrar, $this->anterior($anterior, 'cuentas_por_cobrar'));

        return [
            Stat::make('Ingresos por reservas', MonedaHelper::formatear($ingresos))
                ->description($reservas.' reservas · '.$this->textoDelta($deltaIngresos))
                ->descriptionIcon($this->iconoDelta($deltaIngresos))
                ->descriptionColor($this->colorDelta($deltaIngresos))
                ->chart($this->pluckSerie($series, 'ingresos_por_dia', 'total'))
                ->chartColor('#16A34A')
                ->color('success'),

            Stat::make('Ocupación estimada', number_format($ocupacion, 1).'%')
                ->description('ADR '.MonedaHelper::formatear($adr).' · RevPAR '.MonedaHelper::formatear($revpar).' · '.$this->textoDelta($deltaOcupacion, 'pp'))
                ->descriptionIcon($this->iconoDelta($deltaOcupacion))
                ->descriptionColor($this->colorDelta($deltaOcupacion))
                ->chart($this->pluckSerie($series, 'ocupacion_por_dia', 'ocupacion'))
                ->chartColor('#6b003e')
                ->color('info'),

            Stat::make('Cobrado', MonedaHelper::formatear($cobrado))
                ->description('Pagos recibidos · '.$this->textoDelta($deltaCobrado))
                ->descriptionIcon($this->iconoDelta($deltaCobrado))
                ->descriptionColor($this->colorDelta($deltaCobrado))
                ->color('primary'),

            Stat::make('Facturado (fiscal)', MonedaHelper::formatear($facturado))
                ->description('Documentos emitidos · '.$this->textoDelta($deltaFacturado))
                ->descriptionIcon($this->iconoDelta($deltaFacturado))
                ->descriptionColor($this->colorDelta($deltaFacturado))
                ->chart($this->pluckSerie($series, 'facturado_por_dia', 'total'))
                ->chartColor('#0EA5E9')
                ->color('info'),

            Stat::make('Restaurante', MonedaHelper::formatear($restaurante))
                ->description('Ventas operativas · '.$this->textoDelta($deltaRestaurante))
                ->descriptionIcon($this->iconoDelta($deltaRestaurante))
                ->descriptionColor($this->colorDelta($deltaRestaurante))
                ->color('warning'),

            Stat::make('Cuentas por cobrar', MonedaHelper::formatear($cuentasPorCobrar))
                ->description('Saldo pendiente · '.$this->textoDelta($deltaCuentasPorCobrar))
                ->descriptionIcon($this->iconoDelta($deltaCuentasPorCobrar))
                ->descriptionColor($this->colorDelta($deltaCuentasPorCobrar))
                ->color('danger'),
        ];
    }

    /**
     * @param  array<string, int|float>  $anterior
     */
    private function anterior(array $anterior, string $key): float
    {
        $valor = $anterior[$key] ?? null;

        return is_numeric($valor) ? (float) $valor : 0.0;
    }

    private function deltaPct(float $actual, float $anterior): ?float
    {
        if (abs($anterior) < 0.005) {
            return $actual > 0 ? null : 0.0;
        }

        return (($actual - $anterior) / abs($anterior)) * 100;
    }

    private function deltaPuntos(float $actual, float $anterior): float
    {
        return $actual - $anterior;
    }

    private function textoDelta(?float $delta, string $unidad = '%'): string
    {
        if ($delta === null) {
            return 'sin referencia previa';
        }

        $margen = $unidad === 'pp' ? 0.01 : 0.05;
        if (abs($delta) < $margen) {
            return 'igual al período anterior';
        }

        $signo = $delta > 0 ? '+' : '';

        return "vs período anterior {$signo}".number_format($delta, 1).$unidad;
    }

    private function iconoDelta(?float $delta): Heroicon
    {
        if ($delta === null || abs($delta) < 0.05) {
            return Heroicon::ArrowRightOnRectangle;
        }

        return $delta > 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown;
    }

    private function colorDelta(?float $delta): string
    {
        if ($delta === null || abs($delta) < 0.05) {
            return 'gray';
        }

        return $delta > 0 ? 'success' : 'danger';
    }

    /**
     * @param  array<string, mixed>  $series
     * @return array<int, float>
     */
    private function pluckSerie(array $series, string $key, string $campo): array
    {
        $datos = is_array($series[$key] ?? null) ? $series[$key] : [];

        return collect($datos)
            ->pluck($campo)
            ->map(fn (mixed $valor): float => is_numeric($valor) ? (float) $valor : 0.0)
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function dashboard(): array
    {
        $rango = $this->rangoDashboard();

        return app(InteligenciaNegocioDashboardQuery::class)->paraRango(
            $rango['inicio'],
            $rango['fin'],
        );
    }
}
