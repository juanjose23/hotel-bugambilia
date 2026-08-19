<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoPagoReserva;
use App\Filament\Shared\Concerns\ManejaPaginaReporte;
use App\Filament\Shared\Forms\CategoriaSelect;
use App\Repository\Queries\Reservas\Reportes\ObtenerMetricasReporteReservasQuery;
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
class ReportesReservas extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms, ManejaPaginaReporte;

    protected ObtenerMetricasReporteReservasQuery $metricasQuery;

    public function getModuloReportes(): string
    {
        return 'reservas';
    }

    protected string $view = 'filament.resources.reservas.reportes-reservas';

    protected static ?string $slug = 'reportes-reservas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Gestión de Reservas';

    protected static ?string $navigationLabel = 'Reportes & Ventas';

    protected static ?string $title = 'Centro de Reportes de Reservas y Ventas';

    protected static ?int $navigationSort = 99;

    /** @var array<string, mixed> */
    public ?array $reportData = [];

    public function boot(ObtenerMetricasReporteReservasQuery $metricasQuery): void
    {
        $this->metricasQuery = $metricasQuery;
    }

    public function mount(): void
    {
        $this->reportData = [
            'reporte' => null,
            'estado' => null,
            'tipo_pago' => null,
            'categoria_id' => null,
            'fecha_inicio' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ];
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return $this->metricasQuery->ejecutar();
    }

    /** @return array<string, mixed> */
    protected function getForms(): array
    {
        return [
            'reportForm' => $this->makeSchema()
                ->schema([
                    Select::make('reporte')
                        ->label('Reporte Analítico')
                        ->options(ReporteConfig::getSelectOptions('reservas'))
                        ->required()
                        ->live()
                        ->native(false)
                        ->searchable()
                        ->placeholder('Selecciona un reporte de la lista...'),

                    TextEntry::make('reporte_descripcion')
                        ->hiddenLabel()
                        ->state(fn ($get) => ReporteConfig::getDescripcion('reservas', $get('reporte')) ?? 'Seleccione un reporte de la lista para ver su descripción...')
                        ->extraAttributes(['class' => 'text-sm text-gray-500 italic mt-1 dark:text-gray-400']),

                    Select::make('estado')
                        ->label('Filtrar por Estado de Reserva (Opcional)')
                        ->options(EstadoReserva::class)
                        ->placeholder('Todos los estados')
                        ->native(false)
                        ->visible(fn ($get) => in_array($get('reporte'), ['reservas_estado', 'ocupacion'], true)),

                    Select::make('tipo_pago')
                        ->label('Filtrar por Canal / Garantía (Opcional)')
                        ->options(TipoPagoReserva::class)
                        ->placeholder('Todos los métodos')
                        ->native(false)
                        ->visible(fn ($get) => $get('reporte') === 'ventas_ingresos'),

                    CategoriaSelect::make(CatalogoTipo::CATEGORIA_HABITACION, label: 'Filtrar por Categoría de Habitación')
                        ->placeholder('Todas las categorías')
                        ->visible(fn ($get) => $get('reporte') === 'rendimiento_habitaciones'),

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
            'estado' => $data['estado'] ?? null,
            'tipo_pago' => $data['tipo_pago'] ?? null,
            'categoria_id' => $data['categoria_id'] ?? null,
            'pageSize' => $this->pageSize,
            'orientation' => $this->orientation,
        ];

        try {
            $url = ReporteConfig::getUrl('reservas', $reporte, $params, 'pdf');
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
            || $user->can('page_ReportesReservas')
            || $user->can('Reservas:ReporteOcupacion')
            || $user->can('Reservas:ReporteVentasIngresos')
            || $user->can('Reservas:ReporteEstado')
            || $user->can('Reservas:ReporteHuespedes')
            || $user->can('Reservas:ReporteRendimiento');
    }
}
