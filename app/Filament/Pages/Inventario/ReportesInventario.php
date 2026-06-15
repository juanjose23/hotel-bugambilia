<?php

declare(strict_types=1);

namespace App\Filament\Pages\Inventario;

use App\Models\Catalogos\Producto;
use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesCuarentena;
use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesProximosVencer;
use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesVencidos;
use App\UseCases\Inventario\Queries\Gestion\ObtenerRotacionInventario;
use App\UseCases\Inventario\Queries\Mermas\ObtenerLotesMerma;
use App\UseCases\Inventario\Queries\Mermas\ObtenerMermasTotales;
use App\UseCases\Inventario\Queries\Stock\ObtenerStockPorProducto;
use App\UseCases\Inventario\Queries\Stock\ObtenerValorizacionInventario;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use UnitEnum;

class ReportesInventario extends Page
{
    protected string $view = 'filament.pages.inventario.reportes-inventario';

    protected static ?string $slug = 'reportes-inventario';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?string $navigationLabel = 'Reportes de Inventario';

    protected static ?string $title = 'Reportes del Módulo de Inventario';

    protected static ?int $navigationSort = 99;

    // ─── Datos de cada sección ────────────────────────────────────────────
    /** @var Collection<int, mixed>|null */
    public ?Collection $stockPorProducto = null;

    /** @var Collection<int, mixed>|null */
    public ?Collection $lotesCuarentena = null;

    /** @var Collection<int, mixed>|null */
    public ?Collection $lotesProximosVencer = null;

    /** @var Collection<int, mixed>|null */
    public ?Collection $lotesVencidos = null;

    /** @var Collection<int, mixed>|null */
    public ?Collection $lotesMerma = null;

    /** @var Collection<int, mixed>|null */
    public ?Collection $valorizacion = null;

    /** @var Collection<int, mixed>|null */
    public ?Collection $rotacion = null;

    /** @var Collection<int, mixed>|null */
    public ?Collection $mermasTotales = null;

    public ?float $valorTotalInventario = null;

    public ?float $totalPerdidas = null;

    public function mount(): void
    {
        // Cargar resúmenes rápidos al montar la página
        $this->stockPorProducto = app(ObtenerStockPorProducto::class)->ejecutar();
        $this->lotesCuarentena = app(ObtenerLotesCuarentena::class)->ejecutar();
        $this->lotesProximosVencer = app(ObtenerLotesProximosVencer::class)->ejecutar(['dias' => 30]);
        $this->lotesVencidos = app(ObtenerLotesVencidos::class)->ejecutar();
        $this->lotesMerma = app(ObtenerLotesMerma::class)->ejecutar([
            'periodo_desde' => now()->startOfMonth(),
            'periodo_hasta' => now(),
        ]);
        $this->valorizacion = app(ObtenerValorizacionInventario::class)->ejecutar();
        $this->valorTotalInventario = app(ObtenerValorizacionInventario::class)->totalGeneral();
        $this->rotacion = app(ObtenerRotacionInventario::class)->ejecutar(['meses' => 3]);
        $this->mermasTotales = app(ObtenerMermasTotales::class)->ejecutar([
            'periodo_desde' => now()->startOfMonth(),
            'periodo_hasta' => now(),
        ]);
        $this->totalPerdidas = app(ObtenerMermasTotales::class)->totalPerdidas([
            'periodo_desde' => now()->startOfMonth(),
            'periodo_hasta' => now(),
        ]);
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.inventario.reportes-header');
    }

    public function getHeaderActions(): array
    {
        return [
            // 1. Inventario de Productos
            Action::make('descargar_stock')
                ->label('Generar Reporte de Inventario')
                ->modalHeading('Reporte de Inventario de Productos')
                ->modalDescription('Filtra y descarga el stock disponible actual de tus almacenes.')
                ->schema([
                    Select::make('formato')
                        ->label('Formato de descarga')
                        ->options([
                            'pdf' => 'Documento PDF (Listo para imprimir)',
                            'excel' => 'Hoja de Cálculo Excel (.xlsx)',
                        ])
                        ->default('pdf')
                        ->required(),
                    Select::make('producto_id')
                        ->label('Filtrar por Producto (Opcional)')
                        ->options(fn () => Producto::query()->pluck('nombre', 'id')->toArray())
                        ->searchable()
                        ->placeholder('Todos los productos'),
                ])
                ->action(function (array $data) {
                    $route = $data['formato'] === 'pdf'
                        ? 'reporte.inventario.stock-producto.pdf'
                        : 'reporte.inventario.stock-producto.excel';

                    return redirect()->route($route, [
                        'producto_id' => $data['producto_id'] ?? null,
                    ]);
                }),

            // 2. Lotes Vencidos
            Action::make('descargar_vencidos')
                ->label('Generar Reporte de Productos Vencidos')
                ->modalHeading('Reporte de Lotes Vencidos (Expirados)')
                ->modalDescription('Descarga la lista de lotes cuya fecha de vencimiento ya expiró.')
                ->schema([
                    Select::make('formato')
                        ->label('Formato de descarga')
                        ->options([
                            'pdf' => 'Documento PDF (Listo para imprimir)',
                            'excel' => 'Hoja de Cálculo Excel (.xlsx)',
                        ])
                        ->default('pdf')
                        ->required(),
                    Select::make('producto_id')
                        ->label('Filtrar por Producto (Opcional)')
                        ->options(fn () => Producto::query()->pluck('nombre', 'id')->toArray())
                        ->searchable()
                        ->placeholder('Todos los productos'),
                ])
                ->action(function (array $data) {
                    $route = $data['formato'] === 'pdf'
                        ? 'reporte.inventario.vencidos.pdf'
                        : 'reporte.inventario.vencidos.excel';

                    return redirect()->route($route, [
                        'producto_id' => $data['producto_id'] ?? null,
                    ]);
                }),

            // 3. Próximos Vencimientos
            Action::make('descargar_proximos_vencer')
                ->label('Generar Reporte de Próximos Vencimientos')
                ->modalHeading('Reporte de Próximos Vencimientos')
                ->modalDescription('Filtra los productos que expiran en los siguientes días.')
                ->schema([
                    Select::make('formato')
                        ->label('Formato de descarga')
                        ->options([
                            'pdf' => 'Documento PDF (Listo para imprimir)',
                            'excel' => 'Hoja de Cálculo Excel (.xlsx)',
                        ])
                        ->default('pdf')
                        ->required(),
                    TextInput::make('dias')
                        ->label('Días de anticipación')
                        ->numeric()
                        ->default(30)
                        ->minValue(1)
                        ->required(),
                    Select::make('producto_id')
                        ->label('Filtrar por Producto (Opcional)')
                        ->options(fn () => Producto::query()->pluck('nombre', 'id')->toArray())
                        ->searchable()
                        ->placeholder('Todos los productos'),
                ])
                ->action(function (array $data) {
                    $route = $data['formato'] === 'pdf'
                        ? 'reporte.inventario.proximos-vencer.pdf'
                        : 'reporte.inventario.proximos-vencer.excel';

                    return redirect()->route($route, [
                        'dias' => $data['dias'],
                        'producto_id' => $data['producto_id'] ?? null,
                    ]);
                }),

            // 4. Cuarentena
            Action::make('descargar_cuarentena')
                ->label('Generar Reporte de Cuarentena')
                ->modalHeading('Reporte de Productos en Cuarentena')
                ->modalDescription('Descarga la lista de lotes retenidos por calidad en bodega.')
                ->schema([
                    Select::make('formato')
                        ->label('Formato de descarga')
                        ->options([
                            'pdf' => 'Documento PDF (Listo para imprimir)',
                            'excel' => 'Hoja de Cálculo Excel (.xlsx)',
                        ])
                        ->default('pdf')
                        ->required(),
                    Select::make('producto_id')
                        ->label('Filtrar por Producto (Opcional)')
                        ->options(fn () => Producto::query()->pluck('nombre', 'id')->toArray())
                        ->searchable()
                        ->placeholder('Todos los productos'),
                ])
                ->action(function (array $data) {
                    $route = $data['formato'] === 'pdf'
                        ? 'reporte.inventario.cuarentena.pdf'
                        : 'reporte.inventario.cuarentena.excel';

                    return redirect()->route($route, [
                        'producto_id' => $data['producto_id'] ?? null,
                    ]);
                }),

            // 5. Valorización
            Action::make('descargar_valorizacion')
                ->label('Generar Reporte de Valorización')
                ->modalHeading('Reporte de Valorización Financiera de Almacén')
                ->modalDescription('Genera el costo acumulado de todo el stock activo en Córdobas.')
                ->schema([
                    Select::make('formato')
                        ->label('Formato de descarga')
                        ->options([
                            'pdf' => 'Documento PDF (Listo para imprimir)',
                            'excel' => 'Hoja de Cálculo Excel (.xlsx)',
                        ])
                        ->default('pdf')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $route = $data['formato'] === 'pdf'
                        ? 'reporte.inventario.valorizacion.pdf'
                        : 'reporte.inventario.valorizacion.excel';

                    return redirect()->route($route);
                }),

            // 6. Rotación
            Action::make('descargar_rotacion')
                ->label('Generar Reporte de Rotación')
                ->modalHeading('Reporte de Rotación de Inventario')
                ->modalDescription('Analiza el movimiento promedio mensual de tus artículos.')
                ->schema([
                    TextInput::make('meses')
                        ->label('Período de análisis (meses)')
                        ->numeric()
                        ->default(3)
                        ->minValue(1)
                        ->required(),
                ])
                ->action(function (array $data) {
                    return redirect()->route('reporte.inventario.rotacion.excel', [
                        'meses' => $data['meses'],
                    ]);
                }),

            // 7. Mermas y Pérdidas
            Action::make('descargar_mermas')
                ->label('Generar Reporte de Mermas')
                ->modalHeading('Reporte de Mermas y Pérdidas')
                ->modalDescription('Filtra los productos desechados o perdidos en un rango de fechas.')
                ->schema([
                    Select::make('formato')
                        ->label('Formato de descarga')
                        ->options([
                            'pdf' => 'Documento PDF (Listo para imprimir)',
                            'excel' => 'Hoja de Cálculo Excel (Detalle)',
                            'totales' => 'Hoja de Cálculo Excel (Resumen de Totales)',
                        ])
                        ->default('pdf')
                        ->required(),
                    DatePicker::make('periodo_desde')
                        ->label('Desde')
                        ->default(now()->startOfMonth())
                        ->required(),
                    DatePicker::make('periodo_hasta')
                        ->label('Hasta')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $params = [
                        'periodo_desde' => $data['periodo_desde'],
                        'periodo_hasta' => $data['periodo_hasta'],
                    ];

                    if ($data['formato'] === 'pdf') {
                        return redirect()->route('reporte.inventario.mermas.pdf', $params);
                    } elseif ($data['formato'] === 'excel') {
                        return redirect()->route('reporte.inventario.mermas.excel', $params);
                    } else {
                        return redirect()->route('reporte.inventario.mermas-totales.excel', $params);
                    }
                }),
        ];
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
        ];

        foreach ($reportPermissions as $perm) {
            if ($user->can($perm)) {
                return true;
            }
        }

        return false;
    }
}
