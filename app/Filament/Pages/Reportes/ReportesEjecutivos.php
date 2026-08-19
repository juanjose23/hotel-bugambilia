<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reportes;

use App\Filament\Shared\Concerns\ManejaPaginaReporte;
use App\Repository\Queries\Reportes\ObtenerMetricasEjecutivasQuery;
use App\Support\ReporteConfig;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property Schema $reportForm
 */
class ReportesEjecutivos extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms, ManejaPaginaReporte;

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
            'reporte' => null,
            'fecha_inicio' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ];

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

    /** @return array<string, mixed> */
    protected function getForms(): array
    {
        return [
            'reportForm' => $this->makeSchema()
                ->schema([
                    Select::make('reporte')
                        ->label('Reporte Financiero')
                        ->options(ReporteConfig::getSelectOptions('financiero'))
                        ->required()
                        ->live()
                        ->native(false)
                        ->searchable()
                        ->placeholder('Selecciona un reporte de la lista...'),

                    TextEntry::make('reporte_descripcion')
                        ->hiddenLabel()
                        ->state(fn ($get) => ReporteConfig::getDescripcion('financiero', $get('reporte')) ?? 'Seleccione un reporte de la lista para ver su descripción...')
                        ->extraAttributes(['class' => 'text-sm text-gray-500 italic mt-1 dark:text-gray-400']),

                    DatePicker::make('fecha_inicio')
                        ->label('Fecha Inicio')
                        ->default(now()->startOfMonth()->format('Y-m-d'))
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn () => $this->cargarMetricas()),

                    DatePicker::make('fecha_fin')
                        ->label('Fecha Fin')
                        ->default(now()->format('Y-m-d'))
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn () => $this->cargarMetricas()),
                ])
                ->statePath('reportData'),
        ];
    }

    public function descargarReporte(): mixed
    {
        $data = $this->reportForm->getState();
        $rawReporte = $data['reporte'] ?? null;
        $reporte = is_string($rawReporte) ? $rawReporte : '';
        if (! $reporte) {
            return null;
        }

        $params = [
            'fecha_inicio' => $data['fecha_inicio'] ?? now()->startOfMonth()->format('Y-m-d'),
            'fecha_fin' => $data['fecha_fin'] ?? now()->format('Y-m-d'),
            'pageSize' => $this->pageSize,
            'orientation' => $this->orientation,
        ];

        try {
            $url = ReporteConfig::getUrl('financiero', $reporte, $params, 'pdf');
            $this->dispatch('open-new-tab', url: $url);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return null;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
        $roleName = is_string($superAdminRole) ? $superAdminRole : 'super_admin';

        return $user->hasRole($roleName)
            || $user->can('page_ReportesEjecutivos')
            || $user->can('Financiero:ReporteResumenEjecutivo')
            || $user->can('Financiero:ReporteCuentasCobrar')
            || $user->can('Financiero:ReporteFacturacionVentas');
    }
}
