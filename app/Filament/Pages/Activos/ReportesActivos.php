<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Filament\Pages\Activos\Widgets\EstadisticasActivosWidget;
use App\Filament\Pages\Activos\Widgets\MantenimientosVencidosWidget;
use App\Filament\Pages\Activos\Widgets\ProximosMantenimientosWidget;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Support\ReporteConfig;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
class ReportesActivos extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms;

    protected string $view = 'filament.resources.activos.reportes-activos';

    protected static ?string $slug = 'reportes-activos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Activos Fijos';

    protected static ?string $navigationLabel = 'Reportes de Activos';

    protected static ?string $title = 'Reportes del Módulo de Activos Fijos';

    protected static ?int $navigationSort = 99;

    /** @var array<string, mixed> */
    public ?array $reportData = [];

    public function mount(): void
    {
        $this->reportData = [
            'reporte' => null,
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

    protected function getHeaderWidgets(): array
    {
        return [
            EstadisticasActivosWidget::class,
            ProximosMantenimientosWidget::class,
            MantenimientosVencidosWidget::class,
        ];
    }

    /** @return array<string, mixed> */
    protected function getForms(): array
    {
        return [
            'reportForm' => $this->makeSchema()
                ->schema([
                    Select::make('reporte')
                        ->label('Seleccionar Reporte')
                        ->options(ReporteConfig::getSelectOptions('activos'))
                        ->required()
                        ->live()
                        ->native(false)
                        ->searchable()
                        ->placeholder('Selecciona un reporte de la lista...'),

                    TextEntry::make('reporte_descripcion')
                        ->hiddenLabel()
                        ->state(fn ($get) => ReporteConfig::getDescripcion('activos', $get('reporte')) ?? 'Seleccione un reporte de la lista para ver su descripción...')
                        ->extraAttributes(['class' => 'text-sm text-gray-500 italic mt-1 dark:text-gray-400']),

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
                        ->label('Tipo')
                        ->options([
                            'habitacion' => 'Habitación',
                            'espacio' => 'Espacio / Área Común',
                        ])
                        ->default('habitacion')
                        ->required()
                        ->live()
                        ->visible(fn ($get) => $get('reporte') === 'hoja_habitacion'),

                    Select::make('entidad_id')
                        ->label('Seleccionar Habitación o Espacio')
                        ->options(fn (callable $get) => $get('tipo') === 'habitacion'
                            ? Habitacion::pluck('nombre', 'id')
                            : Espacio::pluck('nombre', 'id')
                        )
                        ->searchable()
                        ->required()
                        ->visible(fn ($get) => $get('reporte') === 'hoja_habitacion'),

                    Select::make('espacio_id')
                        ->label('Seleccionar Espacio')
                        ->options(Espacio::orderBy('nombre')->pluck('nombre', 'id'))
                        ->searchable()
                        ->required()
                        ->prefixIcon(Heroicon::BuildingStorefront)
                        ->visible(fn ($get) => $get('reporte') === 'ficha_espacio'),

                    TextInput::make('dias')
                        ->label('Días de anticipación')
                        ->numeric()
                        ->default(90)
                        ->minValue(1)
                        ->required()
                        ->visible(fn ($get) => $get('reporte') === 'garantias'),

                    Select::make('activo_id')
                        ->label('Seleccionar Activo')
                        ->options(Activo::pluck('codigo_inventario', 'id'))
                        ->searchable()
                        ->required()
                        ->visible(fn ($get) => $get('reporte') === 'historial'),

                    DatePicker::make('fecha_inicio')
                        ->label('Fecha Inicio')
                        ->default(now()->startOfMonth())
                        ->required()
                        ->native(false),

                    DatePicker::make('fecha_fin')
                        ->label('Fecha Fin')
                        ->default(now())
                        ->required()
                        ->native(false),
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
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin' => $data['fecha_fin'] ?? null,
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

        try {
            $url = ReporteConfig::getUrl('activos', $reporte, $params, 'pdf');
            $this->dispatch('open-new-tab', url: $url);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

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

        return $user->hasRole($roleName)
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
