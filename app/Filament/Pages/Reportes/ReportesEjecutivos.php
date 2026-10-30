<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes;

use App\Filament\Shared\Concerns\ManejaPaginaReporte;
use App\Repository\Queries\Reportes\ObtenerMetricasEjecutivasQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Closure;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property Schema $reportForm
 */
class ReportesEjecutivos extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms, ManejaPaginaReporte {
        ManejaPaginaReporte::getForms insteadof InteractsWithForms;
    }

    protected ObtenerMetricasEjecutivasQuery $metricasQuery;

    public function getModuloReportes(): string
    {
        return 'financiero';
    }

    protected string $view = 'filament.pages.reportes.reportes-ejecutivos';

    protected static ?string $slug = 'reportes-ejecutivos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartPie;

    protected static string|UnitEnum|null $navigationGroup = 'Inicio & Análisis';

    protected static ?string $navigationLabel = 'Tablero Ejecutivo & Financiero';

    protected static ?string $title = 'Centro de Inteligencia Financiera y Tomador de Decisiones';

    protected static ?int $navigationSort = 1;

    /** @var array<string, mixed> */
    public ?array $reportData = [];

    public float $totalIngresosReservas = 0.0;

    public float $totalRecaudado = 0.0;

    public float $totalCuentasPorCobrar = 0.0;

    public float $totalFacturadoFiscal = 0.0;

    public int $cantidadReservas = 0;

    public function boot(ObtenerMetricasEjecutivasQuery $metricasQuery): void
    {
        $this->metricasQuery = $metricasQuery;
    }

    public function mount(): void
    {
        $this->reportData = [
            'reporte' => 'resumen_ejecutivo',
            'fecha_inicio' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ];

        $this->reportForm->fill($this->reportData);
        $this->cargarMetricas();
    }

    public function cargarMetricas(?ObtenerMetricasEjecutivasQuery $metricasQuery = null): void
    {
        $query = $metricasQuery ?? $this->metricasQuery;

        $rawFechaInicio = $this->reportData['fecha_inicio'] ?? null;
        $fechaInicio = is_string($rawFechaInicio) ? $rawFechaInicio : now()->startOfMonth()->format('Y-m-d');

        $rawFechaFin = $this->reportData['fecha_fin'] ?? null;
        $fechaFin = is_string($rawFechaFin) ? $rawFechaFin : now()->format('Y-m-d');

        $metricas = $query->ejecutar($fechaInicio, $fechaFin);

        $this->totalIngresosReservas = $metricas['totalIngresosReservas'];
        $this->totalRecaudado = $metricas['totalRecaudado'];
        $this->cantidadReservas = $metricas['cantidadReservas'];
        $this->totalCuentasPorCobrar = $metricas['totalCuentasPorCobrar'];
        $this->totalFacturadoFiscal = $metricas['totalFacturadoFiscal'];
    }

    protected function getLabelReporte(): string
    {
        return 'Reporte Financiero';
    }

    protected function getOnFechasActualizadasCallback(): ?Closure
    {
        return fn () => $this->cargarMetricas();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->can('Page:ReportesEjecutivos')
            || $user->can('Financiero:ReporteResumenEjecutivo')
            || $user->can('Financiero:ReporteCuentasCobrar')
            || $user->can('Financiero:ReporteFacturacionVentas');
    }
}
