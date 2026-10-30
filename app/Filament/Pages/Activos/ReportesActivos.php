<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Filament\Shared\Concerns\ManejaPaginaReporte;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Queries\Activos\Reportes\ObtenerMetricasReporteActivosQuery;
use App\Repository\Queries\Activos\Reportes\ObtenerOpcionesReportesActivosQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property Schema $reportForm
 */
class ReportesActivos extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms, ManejaPaginaReporte {
        ManejaPaginaReporte::getForms insteadof InteractsWithForms;
    }

    protected ObtenerMetricasReporteActivosQuery $metricasQuery;

    protected ObtenerOpcionesReportesActivosQuery $opcionesQuery;

    public function getModuloReportes(): string
    {
        return 'activos';
    }

    protected string $view = 'filament.resources.activos.reportes-activos';

    protected static ?string $slug = 'reportes-activos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Activos & Mantenimiento';

    protected static ?string $navigationLabel = 'Reportes de Activos';

    protected static ?string $title = 'Reportes del Módulo de Activos Fijos';

    protected static ?int $navigationSort = 99;

    /** @var array<string, mixed> */
    public ?array $reportData = [];

    public function boot(
        ObtenerMetricasReporteActivosQuery $metricasQuery,
        ObtenerOpcionesReportesActivosQuery $opcionesQuery,
    ): void {
        $this->metricasQuery = $metricasQuery;
        $this->opcionesQuery = $opcionesQuery;
    }

    public function mount(): void
    {
        $this->reportData = [
            'reporte' => 'inventario_general',
            'estado' => null,
            'ubicacion_tipo' => null,
            'tipo' => 'habitacion',
            'entidad_id' => null,
            'espacio_id' => null,
            'dias' => 90,
            'activo_id' => null,
            'fecha_inicio' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ];
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return $this->metricasQuery->ejecutar();
    }

    /** @return array<int, mixed> */
    protected function getCamposFiltrosEspecificos(): array
    {
        return [
            Select::make('estado')
                ->label('Filtrar por Estado (Opcional)')
                ->options(EstadoActivo::class)
                ->placeholder('Todos los estados')
                ->native(false)
                ->visible(fn ($get) => $get('reporte') === 'inventario_general'),

            Select::make('ubicacion_tipo')
                ->label('Filtrar por Tipo de Ubicación (Opcional)')
                ->options([
                    Habitacion::class => 'Habitaciones',
                    Espacio::class => 'Espacios / Áreas Comunes',
                ])
                ->placeholder('Todas las ubicaciones')
                ->native(false)
                ->visible(fn ($get) => $get('reporte') === 'por_ubicacion'),

            Radio::make('tipo')
                ->label('Tipo de Entidad')
                ->options([
                    'habitacion' => 'Habitación',
                    'espacio' => 'Espacio / Área Común',
                ])
                ->default('habitacion')
                ->required()
                ->live()
                ->inline()
                ->visible(fn ($get) => $get('reporte') === 'hoja_habitacion'),

            Select::make('entidad_id')
                ->label('Seleccionar Habitación o Espacio')
                ->options(fn (callable $get) => $get('tipo') === 'habitacion'
                    ? $this->opcionesQuery->opcionesHabitaciones()
                    : $this->opcionesQuery->opcionesEspacios()
                )
                ->searchable()
                ->required()
                ->visible(fn ($get) => $get('reporte') === 'hoja_habitacion'),

            Select::make('espacio_id')
                ->label('Seleccionar Espacio')
                ->options(fn () => $this->opcionesQuery->opcionesEspacios())
                ->searchable()
                ->required()
                ->visible(fn ($get) => $get('reporte') === 'ficha_espacio'),

            TextInput::make('dias')
                ->label('Días de anticipación')
                ->numeric()
                ->default(90)
                ->minValue(1)
                ->required()
                ->visible(fn ($get) => $get('reporte') === 'garantias'),

            Select::make('activo_id')
                ->label('Seleccionar Activo Específico')
                ->options(fn () => $this->opcionesQuery->opcionesActivos())
                ->searchable()
                ->required()
                ->visible(fn ($get) => $get('reporte') === 'historial'),
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
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'pageSize' => $this->pageSize,
            'orientation' => $this->orientation,
        ];
        if ($reporte === 'inventario_general') {
            $params['estado'] = $data['estado'] ?? null;
        }
        if ($reporte === 'por_ubicacion') {
            $params['ubicacion_tipo'] = $data['ubicacion_tipo'] ?: null;
        }
        if ($reporte === 'hoja_habitacion') {
            $params['tipo'] = $data['tipo'];
            $params['id'] = $data['entidad_id'];
        }
        if ($reporte === 'espacios_asignados') {
            $params['ubicacion_tipo'] = Espacio::class;
        }
        if ($reporte === 'ficha_espacio') {
            $params['tipo'] = 'espacio';
            $params['id'] = $data['espacio_id'];
        }
        if ($reporte === 'garantias') {
            $params['dias'] = $data['dias'];
        }
        if ($reporte === 'historial') {
            $params['activo_id'] = $data['activo_id'];
        }

        $this->procesarDescargaReporte('activos', $reporte, $params);

        return null;
    }

    // ─── Permisos ────────────────────────────────────────────────────────────

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
        $roleName = is_string($superAdminRole) ? $superAdminRole : 'super_admin';

        return $user->can('Page:ReportesActivos')
            || self::tieneAlgunPermisoReporte();
    }

    private static function tieneAlgunPermisoReporte(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->can('Activos:ReporteInventario')
            || $user->can('Activos:ReportePorUbicacion')
            || $user->can('Activos:ReporteHistorial')
            || $user->can('Activos:ReporteMantenimientoActivos')
            || $user->can('Activos:ReporteHojaHabitacion')
            || $user->can('Activos:ReporteFicha')
            || $user->can('Activos:ReporteEtiquetas');
    }
}
