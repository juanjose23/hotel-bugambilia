<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Enums\Inventario\EstadoLote;
use App\Enums\Restaurante\UbicacionCocina;
use App\Filament\Shared\Forms\ProductoSelect;
use App\Filament\Shared\Forms\ProductoVarianteSelect;
use App\Interactors\Restaurante\Cocina\TransformarMateriaPrimaCocina;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Restaurante\TransformacionMateriaPrima;
use App\Repository\Models\Shared\Stock;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Cocina\ObtenerHistorialTransformacionesCocina;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * @property Schema $form
 */
final class MateriaPrimaCocina extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante & Cocina';

    protected static ?string $navigationLabel = 'Materia Prima Cocina';

    protected static ?string $title = 'Materia Prima Cocina';

    protected static ?string $slug = 'restaurante/materia-prima-cocina';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.restaurante.materia-prima-cocina';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'ubicacion_origen_id' => self::ubicacionCocinaId(),
            'cantidad_procesada' => 1,
            'items' => [[]],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('materia_prima_tabs')
                    ->tabs([
                        Tab::make('Registrar Transformación')
                            ->icon('heroicon-o-beaker')
                            ->schema([
                                Section::make('Producto a transformar')
                                    ->description('Seleccione el producto real que saldrá de inventario para convertirse en materia prima.')
                                    ->icon('heroicon-o-arrow-path')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        Select::make('producto_origen_id')
                                            ->label('Producto origen')
                                            ->options(fn (): array => self::opcionesProductosMateriaPrimaCocina())
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->live()
                                            ->required()
                                            ->helperText('Producto bruto disponible en inventario.')
                                            ->afterStateUpdated(function (Set $set): void {
                                                $set('variante_origen_id', null);
                                            }),

                                        Select::make('variante_origen_id')
                                            ->label('Variante origen')
                                            ->options(fn (Get $get): array => self::opcionesVariantesMateriaPrimaCocina($get('producto_origen_id')))
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->live()
                                            ->required()
                                            ->helperText('Muestra unidades y stock disponible.')
                                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                                self::recalcularCostosTransformacion($get, $set);
                                            }),

                                        Select::make('ubicacion_origen_id')
                                            ->label('Ubicación origen')
                                            ->options(fn (): array => self::opcionesUbicacionCocina())
                                            ->default(fn (): ?int => self::ubicacionCocinaId())
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->required()
                                            ->helperText('Bodega o sub-área de cocina desde donde se retirará el insumo.')
                                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                                self::recalcularCostosTransformacion($get, $set);
                                            }),

                                        TextInput::make('cantidad_procesada')
                                            ->label('Cantidad a transformar')
                                            ->numeric()
                                            ->minValue(0.001)
                                            ->live(onBlur: true)
                                            ->required()
                                            ->helperText('Cantidad bruta que saldrá de inventario.')
                                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                                self::recalcularCostosTransformacion($get, $set);
                                            }),

                                        Textarea::make('observaciones')
                                            ->label('Observaciones')
                                            ->rows(2)
                                            ->placeholder('Ej. Se lavaron y pelaron 4 aguacates para preparar guacamole de la semana.')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Materia prima obtenida y merma')
                                    ->description('Registre los insumos netos útiles que entran a inventario. Los costos se distribuyen automáticamente según las porciones.')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->schema([
                                        Repeater::make('items')
                                            ->label('Resultados del porcionado / transformación')
                                            ->itemLabel(fn (array $state): string => ! empty($state['es_merma']) ? 'Merma / Desperdicio (C$ 0)' : 'Insumo Procesado Útil')
                                            ->collapsible()
                                            ->live()
                                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                                self::recalcularCostosTransformacion($get, $set);
                                            })
                                            ->schema([
                                                Grid::make([
                                                    'default' => 1,
                                                    'md' => 3,
                                                ])
                                                    ->schema([
                                                        Toggle::make('es_merma')
                                                            ->label('¿Es Merma / Desperdicio?')
                                                            ->helperText('Merma = C$ 0 costo')
                                                            ->live()
                                                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                                                self::recalcularCostosTransformacion($get, $set);
                                                            })
                                                            ->columnSpanFull(),

                                                        ProductoSelect::make('producto_destino_id')
                                                            ->live()
                                                            ->required()
                                                            ->afterStateUpdated(fn (Set $set): mixed => $set('variante_destino_id', null)),

                                                        ProductoVarianteSelect::make('variante_destino_id', 'producto_destino_id')
                                                            ->required(fn (Get $get): bool => ! (bool) $get('es_merma'))
                                                            ->hidden(fn (Get $get): bool => (bool) $get('es_merma')),

                                                        Select::make('ubicacion_destino_id')
                                                            ->label('Ubicación destino')
                                                            ->options(fn (): array => self::opcionesUbicaciones())
                                                            ->searchable()
                                                            ->preload()
                                                            ->required(fn (Get $get): bool => ! (bool) $get('es_merma'))
                                                            ->hidden(fn (Get $get): bool => (bool) $get('es_merma')),

                                                        TextInput::make('cantidad')
                                                            ->label('Cantidad')
                                                            ->numeric()
                                                            ->minValue(0.001)
                                                            ->live(onBlur: true)
                                                            ->required()
                                                            ->helperText('Cantidad obtenida/desperdiciada')
                                                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                                                self::recalcularCostosTransformacion($get, $set);
                                                            }),

                                                        TextInput::make('costo_asignado')
                                                            ->label('Costo asignado (C$)')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->minValue(0)
                                                            ->helperText('Calculado automáticamente'),

                                                        TextInput::make('observaciones')
                                                            ->label('Notas del ítem')
                                                            ->placeholder('Ej. Merma de piel y semilla'),
                                                    ]),
                                            ])
                                            ->defaultItems(1)
                                            ->addActionLabel('Agregar resultado / merma')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Stock de Insumos en Cocina')
                            ->icon('heroicon-o-archive-box')
                            ->schema([
                                ViewField::make('stock_cocina_tabla')
                                    ->view('filament.pages.restaurante.components.stock-cocina-tabla'),
                            ]),

                        Tab::make('Historial y Auditoría')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                ViewField::make('historial_transformaciones_tabla')
                                    ->view('filament.pages.restaurante.components.historial-transformaciones-tabla'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function guardar(): void
    {
        try {
            $usuarioId = Auth::id() !== null ? (int) Auth::id() : null;
            $transformacion = app(TransformarMateriaPrimaCocina::class)->ejecutar($this->form->getState(), $usuarioId);
        } catch (DomainException $exception) {
            Notification::make()->title('No se pudo transformar')->body($exception->getMessage())->danger()->send();

            return;
        }

        Notification::make()
            ->title('Materia prima creada')
            ->body("Transformación {$transformacion->codigo} registrada en inventario.")
            ->success()
            ->send();

        $this->form->fill([
            'ubicacion_origen_id' => self::ubicacionCocinaId(),
            'cantidad_procesada' => 1,
            'items' => [[]],
        ]);
    }

    public function table(Table $table): Table
    {
        $cocinaId = self::ubicacionCocinaId() ?? 0;

        return $table
            ->query(
                Stock::query()
                    ->where('stockable_type', Ubicacion::class)
                    ->where('stockable_id', $cocinaId)
                    ->where('cantidad_actual', '>', 0)
                    ->whereHas('variante.producto.categoria', function (Builder $cat): void {
                        $cat->whereNotIn('nombre', [
                            'Amenities',
                            'Aseo y Limpieza',
                            'Limpieza',
                            'Lencería',
                            'Mantenimiento',
                            'Equipamiento',
                            'Oficina',
                            'Ferretería',
                            'Herramientas',
                        ]);
                    })
                    ->with(['variante.producto.unidadMedida', 'variante.unidadMedida', 'lote'])
            )
            ->columns([
                TextColumn::make('variante.producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('variante.nombre_variante')
                    ->label('Variante / Presentación')
                    ->badge()
                    ->searchable(),

                TextColumn::make('cantidad_actual')
                    ->label('Cantidad Disponible')
                    ->numeric(decimalPlaces: 3)
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('unidad_medida')
                    ->label('Unidad')
                    ->state(fn (Stock $record): string => $record->variante?->unidadMedida->nombre ?? $record->variante?->producto->unidadMedida->nombre ?? 'unid'),

                TextColumn::make('costo_unitario')
                    ->label('Costo Unitario')
                    ->state(fn (Stock $record): float => (float) ($record->lote->costo_unitario ?? 0.0))
                    ->money('NIO')
                    ->alignEnd(),

                TextColumn::make('valor_total')
                    ->label('Valor Total Estimado')
                    ->state(fn (Stock $record): float => (float) $record->cantidad_actual * (float) ($record->lote->costo_unitario ?? 0.0))
                    ->money('NIO')
                    ->alignEnd(),
            ])
            ->defaultSort('id', 'desc');
    }

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('volverCocina')
                ->label('Volver a Cocina')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => CocinaPedidos::getUrl()),
            Action::make('conciliacionRecetas')
                ->label('Conciliar Recetas')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('gray')
                ->url(fn (): string => ConciliacionRecetasCocina::getUrl()),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /** @return array<int, string> */
    private static function opcionesUbicaciones(): array
    {
        /** @var array<int, string> $opciones */
        $opciones = Ubicacion::query()
            ->where('estado', 1)
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();

        return $opciones;
    }

    /** @return array<int, string> */
    private static function opcionesUbicacionCocina(): array
    {
        $cocinaId = self::ubicacionCocinaId();

        if ($cocinaId === null) {
            return [];
        }

        /** @var array<int, string> $opciones */
        $opciones = Ubicacion::query()
            ->whereKey($cocinaId)
            ->pluck('nombre', 'id')
            ->all();

        return $opciones;
    }

    /** @return array<int, string> */
    private static function opcionesProductosMateriaPrimaCocina(): array
    {
        $cocinaId = self::ubicacionCocinaId();

        if ($cocinaId === null) {
            return [];
        }

        $productoIds = self::stockMateriaPrimaCocina()
            ->where('stockable_type', Ubicacion::class)
            ->where('stockable_id', $cocinaId)
            ->whereHas('variante')
            ->with('variante')
            ->get()
            ->map(fn (Stock $stock): ?int => $stock->variante?->producto_id)
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->unique()
            ->values()
            ->all();

        /** @var array<int, string> $opciones */
        $opciones = Producto::query()
            ->whereIn('id', $productoIds)
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();

        return $opciones;
    }

    /** @return array<int, string> */
    private static function opcionesVariantesMateriaPrimaCocina(mixed $productoId): array
    {
        $cocinaId = self::ubicacionCocinaId();

        if ($cocinaId === null || ! is_numeric($productoId)) {
            return [];
        }

        $stockPorVariante = self::stockMateriaPrimaCocina()
            ->where('stockable_type', Ubicacion::class)
            ->where('stockable_id', $cocinaId)
            ->whereHas('variante', fn ($q) => $q->where('producto_id', (int) $productoId))
            ->with('variante')
            ->get()
            ->groupBy('producto_variante_id')
            ->map(function ($stocks): float {
                $val = $stocks->sum('cantidad_actual');

                return is_numeric($val) ? (float) $val : 0.0;
            });

        $varianteIds = $stockPorVariante
            ->keys()
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        /** @var array<int, string> $opciones */
        $opciones = ProductoVariante::query()
            ->whereIn('id', $varianteIds)
            ->orderBy('codigo')
            ->get()
            ->mapWithKeys(function (ProductoVariante $variante) use ($stockPorVariante): array {
                $nombre = $variante->nombre_variante ?: $variante->codigo;
                $cantidad = number_format((float) ($stockPorVariante->get($variante->id) ?? 0), 3);

                return [(int) $variante->id => "{$variante->codigo} - {$nombre} ({$cantidad} disp.)"];
            })
            ->all();

        return $opciones;
    }

    private static function ubicacionCocinaId(): ?int
    {
        $id = Ubicacion::query()
            ->where('nombre', UbicacionCocina::RESTAURANTE->value)
            ->value('id');

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * @return Builder<Stock>
     */
    private static function stockMateriaPrimaCocina(): Builder
    {
        return Stock::query()
            ->where('cantidad_actual', '>', 0)
            ->whereNotNull('producto_variante_id')
            ->whereHas('variante.producto.categoria', function (Builder $cat): void {
                $cat->whereNotIn('nombre', [
                    'Amenities',
                    'Aseo y Limpieza',
                    'Limpieza',
                    'Lencería',
                    'Mantenimiento',
                    'Equipamiento',
                    'Oficina',
                    'Ferretería',
                    'Herramientas',
                ]);
            })
            ->where(function ($query): void {
                $query->whereNull('lote_id')
                    ->orWhereHas('lote', function ($lote): void {
                        $lote->where('estado', EstadoLote::Disponible)
                            ->where(function ($fecha): void {
                                $fecha->whereNull('fecha_vencimiento')
                                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                            });
                    });
            });
    }

    /**
     * Recalcula automáticamente el costo asignado a cada ítem porcionado en base a la proporción
     * del peso/cantidad neta respecto al costo total del producto origen retirado de inventario.
     */
    private static function recalcularCostosTransformacion(Get $get, Set $set): void
    {
        $ubicacionOrigenId = $get('ubicacion_origen_id');
        $varianteOrigenId = $get('variante_origen_id');
        $cantRaw = $get('cantidad_procesada');
        $cantidadProcesada = is_numeric($cantRaw) ? (float) $cantRaw : 0.0;

        if ($cantidadProcesada <= 0 || ! is_numeric($varianteOrigenId) || ! is_numeric($ubicacionOrigenId)) {
            return;
        }

        $costoUnitarioOrigen = self::obtenerCostoUnitarioVariante((int) $ubicacionOrigenId, (int) $varianteOrigenId);
        $costoTotalOrigen = round($cantidadProcesada * $costoUnitarioOrigen, 2);

        /** @var array<int, array<string, mixed>> $items */
        $items = (array) ($get('items') ?? []);

        if (count($items) === 0) {
            return;
        }

        // Sumar la cantidad de ítems útiles (sin merma)
        $sumaCantidadUtil = 0.0;
        foreach ($items as $item) {
            $cRaw = $item['cantidad'] ?? 0;
            $cantidad = is_numeric($cRaw) ? (float) $cRaw : 0.0;
            if ($cantidad > 0 && empty($item['es_merma'])) {
                $sumaCantidadUtil += $cantidad;
            }
        }

        // Si no hay ítems útiles (todos marcados como merma), sumar todo
        if ($sumaCantidadUtil <= 0) {
            foreach ($items as $item) {
                $cRaw = $item['cantidad'] ?? 0;
                $cantidad = is_numeric($cRaw) ? (float) $cRaw : 0.0;
                if ($cantidad > 0) {
                    $sumaCantidadUtil += $cantidad;
                }
            }
        }

        // Asignar los costos en base a la proporción
        foreach ($items as $index => $item) {
            $cRaw = $item['cantidad'] ?? 0;
            $cantidad = is_numeric($cRaw) ? (float) $cRaw : 0.0;
            $esMerma = ! empty($item['es_merma']);

            if ($esMerma) {
                $set("items.{$index}.costo_asignado", 0.0);
            } elseif ($sumaCantidadUtil > 0 && $cantidad > 0) {
                $costoProporcional = round(($cantidad / $sumaCantidadUtil) * $costoTotalOrigen, 2);
                $set("items.{$index}.costo_asignado", $costoProporcional);
            } else {
                $set("items.{$index}.costo_asignado", 0.0);
            }
        }
    }

    private static function obtenerCostoUnitarioVariante(int $ubicacionId, int $varianteId): float
    {
        $stock = Stock::query()
            ->where('stockable_type', Ubicacion::class)
            ->where('stockable_id', $ubicacionId)
            ->where('producto_variante_id', $varianteId)
            ->with('lote')
            ->first();

        if (! $stock instanceof Stock) {
            return 0.0;
        }

        $costoUnitario = $stock->lote?->costo_unitario;

        return is_numeric($costoUnitario) ? (float) $costoUnitario : 0.0;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function obtenerStockCocinaListado(): Collection
    {
        $cocinaId = self::ubicacionCocinaId();

        if ($cocinaId === null) {
            return new Collection;
        }

        /** @var Collection<int, Stock> $stocks */
        $stocks = Stock::query()
            ->where('stockable_type', Ubicacion::class)
            ->where('stockable_id', $cocinaId)
            ->where('cantidad_actual', '>', 0)
            ->with(['variante.producto.unidadMedida', 'variante.unidadMedida', 'lote'])
            ->orderBy('id', 'desc')
            ->get();

        return $stocks;
    }

    /**
     * @return Collection<int, TransformacionMateriaPrima>
     */
    public function obtenerHistorialTransformacionesListado(): Collection
    {
        return app(ObtenerHistorialTransformacionesCocina::class)->obtenerRecientes(20);
    }

    public static function canAccess(): bool
    {
        if (! app(VerificarRestauranteActivo::class)->estaActivo()) {
            return false;
        }

        /** @var User|null $user */
        $user = auth()->user();

        return $user?->can('page_MateriaPrimaCocina') || $user?->can('page_CocinaPedidos') || ($user?->hasRole('super_admin') ?? false);
    }
}
