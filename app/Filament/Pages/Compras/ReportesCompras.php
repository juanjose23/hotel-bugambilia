<?php

declare(strict_types=1);

namespace App\Filament\Pages\Compras;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Concerns\ManejaPaginaReporte;
use App\Filament\Shared\Forms\ReporteFiltros;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\User;
use App\Repository\Queries\Compras\Reportes\BuscarTrazabilidadPorCodigoQuery;
use App\Repository\Queries\Compras\Reportes\ObtenerSolicitudParaReporteQuery;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

/**
 * @property Schema $reportForm
 * @property Schema $searchForm
 */
class ReportesCompras extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable, ManejaPaginaReporte;

    protected ObtenerSolicitudParaReporteQuery $solicitudQuery;

    public function getModuloReportes(): string
    {
        return 'compras';
    }

    protected string $view = 'filament.resources.compras.reportes-compras';

    protected static ?string $slug = 'reportes-compras';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Compras & Proveedores';

    protected static ?string $navigationLabel = 'Reportes de Compras';

    protected static ?string $title = 'Reportes del Módulo de Compras';

    protected static ?int $navigationSort = 99;

    /** @var array<string, mixed> */
    public ?array $reportData = [];

    /** @var array<string, mixed> */
    public ?array $searchData = [];

    public ?int $selectedSolicitudId = null;

    public ?string $selectedCodigo = null;

    public function boot(ObtenerSolicitudParaReporteQuery $solicitudQuery): void
    {
        $this->solicitudQuery = $solicitudQuery;
    }

    public function mount(): void
    {
        $this->reportData = [
            'reporte' => null,
            'fecha_inicio' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
            'estado' => null,
            'meses' => 6,
        ];

        $this->searchData = [
            'codigo' => null,
        ];
    }

    /** @return array<string, mixed> */
    protected function getForms(): array
    {
        return [
            'reportForm' => $this->makeSchema()
                ->schema(ReporteFiltros::getFormSchema())
                ->statePath('reportData'),
            'searchForm' => $this->makeSchema()
                ->schema([
                    TextInput::make('codigo')
                        ->label('Código de Documento')
                        ->placeholder('Ej. SOL-2026-0001, COT-2026-0001, OC-2026-0001')
                        ->required()
                        ->prefixIcon(Heroicon::MagnifyingGlass),
                ])
                ->statePath('searchData'),
        ];
    }

    public function descargarReporte(): null
    {
        $data = $this->reportForm->getState();
        $data['pageSize'] = $this->pageSize;
        $data['orientation'] = $this->orientation;
        $url = ReporteFiltros::getUrlReporte($data);
        $this->dispatch('open-new-tab', url: $url);

        return null;
    }

    public function buscarTrazabilidad(BuscarTrazabilidadPorCodigoQuery $query): null
    {
        $data = $this->searchForm->getState();
        $codigoValue = $data['codigo'] ?? null;
        $codigo = trim(is_string($codigoValue) ? $codigoValue : '');

        if (blank($codigo)) {
            return null;
        }

        $solicitud = $query->ejecutar($codigo);

        if (! $solicitud) {
            Notification::make()
                ->title('Proceso no encontrado')
                ->body("No se encontró ningún registro de compra (Solicitud, Cotización u Orden de Compra) con el código '$codigo'.")
                ->danger()
                ->send();

            return null;
        }

        $this->selectedSolicitudId = $solicitud->id;
        $this->selectedCodigo = $codigo;

        Notification::make()
            ->title('Trazabilidad cargada')
            ->body("Mostrando el flujo para la solicitud {$solicitud->codigo}.")
            ->success()
            ->send();

        return null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Solicitud::query()->latest())
            ->columns([
                TextColumn::make('codigo')
                    ->label('Solicitud')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('departamentoSolicitante.nombre')
                    ->label('Departamento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('colaborador.persona.nombre_completo')
                    ->label('Solicitante')
                    ->searchable(),
                EstadoBadgeColumn::make(EstadoSolicitud::class),
                TextColumn::make('fecha_solicitud')
                    ->label('Fecha Solicitud')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('ver_trazabilidad')
                    ->label('Trazabilidad')
                    ->icon(Heroicon::ArrowPath)
                    ->color('info')
                    ->action(function (Solicitud $record) {
                        $this->selectedSolicitudId = $record->id;
                        $this->selectedCodigo = $record->codigo;
                    }),
                Action::make('descargar_solicitud')
                    ->label('PDF Solicitud')
                    ->icon(Heroicon::DocumentArrowDown)
                    ->color('gray')
                    ->url(fn (Solicitud $record) => route('admin.compras.reportes.solicitud', ['solicitud' => $record->id]))
                    ->openUrlInNewTab(),
                Action::make('descargar_comparativa')
                    ->label('PDF Comparativa')
                    ->icon(Heroicon::TableCells)
                    ->color('gray')
                    ->url(fn (Solicitud $record) => route('admin.compras.reportes.comparativa', ['solicitud' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn (Solicitud $record) => $record->cotizaciones()->count() > 0),
                Action::make('imprimir_trazabilidad')
                    ->label('Imprimir Trazabilidad')
                    ->icon(Heroicon::Printer)
                    ->color('primary')
                    ->url(fn (Solicitud $record) => route('admin.compras.reportes.trazabilidad-completa', ['solicitud' => $record->id]))
                    ->openUrlInNewTab()
                    ->tooltip('Genera el PDF HTB-COM-009 con el proceso completo de esta compra'),
            ]);
    }

    public function selectSolicitud(int $id): void
    {
        $solicitud = Solicitud::find($id);
        if ($solicitud) {
            $this->selectedSolicitudId = $solicitud->id;
            $this->selectedCodigo = $solicitud->codigo;
        }
    }

    public function clearSelected(): void
    {
        $this->selectedSolicitudId = null;
        $this->selectedCodigo = null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $solicitud = null;
        if ($this->selectedSolicitudId) {
            $solicitud = $this->solicitudQuery
                ->ejecutar($this->selectedSolicitudId);
        }

        return [
            'solicitudSeleccionada' => $solicitud,
        ];
    }

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return $user->can('page_ReportesCompras')
            || $user->can('Compras:ImprimirReportesCompras')
            || $user->can('Compras:ImprimirSolicitud');
    }
}
