<?php

declare(strict_types=1);

namespace App\Filament\Pages\Inventario;

use App\Enums\Catalogos\CatalogoTipo;
use App\Filament\Shared\Forms\ProductoSelect;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesCuarentena;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesProximosVencer;
use App\Repository\Queries\Inventario\Alertas\ObtenerLotesVencidos;
use App\Repository\Queries\Inventario\Gestion\ObtenerRotacionInventario;
use App\Repository\Queries\Inventario\Mermas\ObtenerLotesMerma;
use App\Repository\Queries\Inventario\Mermas\ObtenerMermasTotales;
use App\Repository\Queries\Inventario\Stock\ObtenerStockPorProducto;
use App\Repository\Queries\Inventario\Stock\ObtenerValorizacionInventario;
use App\Repository\Queries\Shared\ObtenerMonedaBase;
use App\Support\CachedOptions;
use App\Support\ReporteConfig;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use UnitEnum;

/**
 * @property Schema $reportForm
 */
class ReportesInventario extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms;

    protected string $view = 'filament.resources.inventario.reportes-inventario';

    protected ObtenerMonedaBase $monedaBase;

    protected ObtenerStockPorProducto $stockPorProductoUc;

    protected ObtenerLotesCuarentena $lotesCuarentenaUc;

    protected ObtenerLotesProximosVencer $lotesProximosVencerUc;

    protected ObtenerLotesVencidos $lotesVencidosUc;

    protected ObtenerLotesMerma $lotesMermaUc;

    protected ObtenerValorizacionInventario $valorizacionInventarioUc;

    protected ObtenerRotacionInventario $rotacionInventarioUc;

    protected ObtenerMermasTotales $mermasTotalesUc;

    /** @var array<string, mixed> */
    public ?array $reportData = [];

    public function boot(
        ObtenerMonedaBase $monedaBase,
        ObtenerStockPorProducto $stockPorProductoUc,
        ObtenerLotesCuarentena $lotesCuarentenaUc,
        ObtenerLotesProximosVencer $lotesProximosVencerUc,
        ObtenerLotesVencidos $lotesVencidosUc,
        ObtenerLotesMerma $lotesMermaUc,
        ObtenerValorizacionInventario $valorizacionInventarioUc,
        ObtenerRotacionInventario $rotacionInventarioUc,
        ObtenerMermasTotales $mermasTotalesUc
    ): void {
        $this->monedaBase = $monedaBase;
        $this->stockPorProductoUc = $stockPorProductoUc;
        $this->lotesCuarentenaUc = $lotesCuarentenaUc;
        $this->lotesProximosVencerUc = $lotesProximosVencerUc;
        $this->lotesVencidosUc = $lotesVencidosUc;
        $this->lotesMermaUc = $lotesMermaUc;
        $this->valorizacionInventarioUc = $valorizacionInventarioUc;
        $this->rotacionInventarioUc = $rotacionInventarioUc;
        $this->mermasTotalesUc = $mermasTotalesUc;
    }

    public function mount(): void
    {
        $this->reportData = [
            'reporte' => null,
            'producto_id' => null,
            'categoria_id' => null,
            'dias' => 30,
            'meses' => 3,
            'fecha_desde' => now()->startOfMonth()->format('Y-m-d'),
            'fecha_hasta' => now()->format('Y-m-d'),
        ];
    }

    protected static ?string $slug = 'reportes-inventario';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Reportes de Inventario';

    protected static ?string $title = 'Reportes del Módulo de Inventario';

    protected static ?int $navigationSort = 99;

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $params
     */
    public function extracted(mixed $reporte, array $data, array $params, string $routes): null
    {
        $params['fecha_desde'] = $data['fecha_desde'] ?? null;
        $params['fecha_hasta'] = $data['fecha_hasta'] ?? null;

        if ($reporte === 'mermas') {
            $params['periodo_desde'] = $data['fecha_desde'] ?? null;
            $params['periodo_hasta'] = $data['fecha_hasta'] ?? null;
        }

        $url = route($routes, $params);
        $this->dispatch('open-new-tab', url: $url);

        return null;
    }

    /** @return array<string, mixed> */
    protected function getForms(): array
    {
        return [
            'reportForm' => $this->makeSchema()
                ->schema([
                    Select::make('reporte')
                        ->label('Seleccionar Reporte')
                        ->options(ReporteConfig::getSelectOptions('inventario'))
                        ->required()
                        ->live()
                        ->native(false)
                        ->searchable()
                        ->placeholder('Selecciona un reporte de la lista...'),

                    TextEntry::make('reporte_descripcion')
                        ->hiddenLabel()
                        ->tooltip(fn ($get) => ReporteConfig::getDescripcion('inventario', $get('reporte')) ?? 'Seleccione un reporte de la lista para ver su descripción...')
                        ->extraAttributes(['class' => 'text-sm text-gray-500 italic mt-1 dark:text-gray-400']),

                    ProductoSelect::make()
                        ->label('Filtrar por Producto (Opcional)')
                        ->placeholder('Todos los productos')
                        ->visible(fn ($get) => in_array($get('reporte'), ['stock', 'vencidos', 'proximos_vencer', 'cuarentena', 'ajustes'])),

                    Select::make('categoria_id')
                        ->label('Categoría (Opcional)')
                        ->options(fn () => CachedOptions::catalogos(CatalogoTipo::CATEGORIA_PRODUCTO->value))
                        ->placeholder('Todas las categorías')
                        ->searchable()
                        ->native(false)
                        ->visible(fn ($get) => $get('reporte') === 'stock_minimo'),

                    TextInput::make('dias')
                        ->label('Días de anticipación')
                        ->numeric()
                        ->default(30)
                        ->minValue(1)
                        ->required()
                        ->visible(fn ($get) => $get('reporte') === 'proximos_vencer'),

                    TextInput::make('meses')
                        ->label('Período de análisis (meses)')
                        ->numeric()
                        ->default(3)
                        ->minValue(1)
                        ->required()
                        ->visible(fn ($get) => $get('reporte') === 'rotacion'),

                    DatePicker::make('fecha_desde')
                        ->label('Desde')
                        ->default(now()->startOfMonth())
                        ->required()
                        ->native(false),

                    DatePicker::make('fecha_hasta')
                        ->label('Hasta')
                        ->default(now())
                        ->required()
                        ->native(false),
                ])
                ->statePath('reportData'),
        ];
    }

    public function descargarReporte(): null
    {
        $data = $this->reportForm->getState();
        $rawReporte = $data['reporte'] ?? null;
        $reporte = is_string($rawReporte) ? $rawReporte : '';
        if (! $reporte) {
            return null;
        }

        $params = [];
        if (in_array($reporte, ['stock', 'vencidos', 'proximos_vencer', 'cuarentena', 'ajustes'])) {
            $params['producto_id'] = $data['producto_id'] ?? null;
        }
        if ($reporte === 'stock_minimo') {
            $params['categoria_id'] = $data['categoria_id'] ?? null;
        }
        if ($reporte === 'proximos_vencer') {
            $params['dias'] = $data['dias'] ?? 30;
        }
        if ($reporte === 'rotacion') {
            $params['meses'] = $data['meses'] ?? 3;
        }

        try {
            $route = ReporteConfig::getRuta('inventario', $reporte);

            return $this->extracted($reporte, $data, $params, $route);
        } catch (InvalidArgumentException) {
            Notification::make()
                ->title('Reporte no disponible')
                ->body('Este reporte no está disponible en formato PDF.')
                ->warning()
                ->send();

            return null;
        }
    }

    public function descargarExcel(): null
    {
        $data = $this->reportForm->getState();
        $rawReporte = $data['reporte'] ?? null;
        $reporte = is_string($rawReporte) ? $rawReporte : '';
        if (! $reporte) {
            return null;
        }

        $params = [];
        if (in_array($reporte, ['stock', 'ajustes'])) {
            $params['producto_id'] = $data['producto_id'] ?? null;
        }
        if ($reporte === 'stock_minimo') {
            $params['categoria_id'] = $data['categoria_id'] ?? null;
        }

        try {
            $route = ReporteConfig::getRuta('inventario', $reporte, 'excel');

            return $this->extracted($reporte, $data, $params, $route);
        } catch (InvalidArgumentException) {
            Notification::make()
                ->title('Reporte no disponible')
                ->body('Este reporte no está disponible en formato Excel.')
                ->warning()
                ->send();

            return null;
        }
    }

    protected function getViewData(): array
    {
        $simbolo = $this->monedaBase->ejecutar()?->simbolo;

        return [
            'stockPorProducto' => $this->stockPorProductoUc->ejecutar(),
            'lotesCuarentena' => $this->lotesCuarentenaUc->ejecutar(),
            'lotesProximosVencer' => $this->lotesProximosVencerUc->ejecutar(['dias' => 30]),
            'lotesVencidos' => $this->lotesVencidosUc->ejecutar(),
            'lotesMerma' => $this->lotesMermaUc->ejecutar([
                'periodo_desde' => now()->startOfMonth()->toDateString(),
                'periodo_hasta' => now()->toDateString(),
            ]),
            'valorizacion' => $this->valorizacionInventarioUc->ejecutar(),
            'valorTotalInventario' => $this->valorizacionInventarioUc->totalGeneral(),
            'rotacion' => $this->rotacionInventarioUc->ejecutar(['meses' => 3]),
            'mermasTotales' => $this->mermasTotalesUc->ejecutar([
                'periodo_desde' => now()->startOfMonth()->toDateString(),
                'periodo_hasta' => now()->toDateString(),
            ]),
            'totalPerdidas' => $this->mermasTotalesUc->totalPerdidas([
                'periodo_desde' => now()->startOfMonth()->toDateString(),
                'periodo_hasta' => now()->toDateString(),
            ]),
            'monedaSimbolo' => is_string($simbolo) ? $simbolo : 'C$',
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.inventario.reportes-header');
    }

    public function getHeaderActions(): array
    {
        return [];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $reportPermissions = [
            'Inventario:ReporteStock',
            'Inventario:ReporteMovimientos',
            'Inventario:ReporteCuarentena',
            'Inventario:ReporteProximosVencer',
            'Inventario:ReporteMermas',
            'Inventario:ReporteValorizacion',
            'Inventario:ReporteRotacion',
            'Inventario:ReporteMermasTotales',
            'Inventario:ReporteTrazabilidad',
            'Inventario:ReporteVencidos',
            'Inventario:ReporteStockMinimo',
            'Inventario:ReporteAjustes',
            'Inventario:ReporteCostoVentas',
        ];

        return array_any($reportPermissions, fn ($perm) => $user->can($perm));

    }
}
