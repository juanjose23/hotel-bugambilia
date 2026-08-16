<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\BusinessLogic\Limpieza\Data\ReabastecerItemData;
use App\BusinessLogic\Limpieza\Data\ReabastecerUbicacionData;
use App\Interactors\Inventario\ConsumirStock;
use App\Interactors\Limpieza\Stock\ReabastecerUbicacion;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\Stock;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

/**
 * @property Schema $form
 * @property Schema $consumirForm
 * @property Schema $reabastecerForm
 */
class ControlLavanderia extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    protected string $view = 'filament.resources.limpieza.control-lavanderia';

    protected static ?string $slug = 'limpieza/control-lavanderia';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza';

    protected static ?string $navigationLabel = 'Control de Lavandería';

    protected static ?string $title = 'Control de Inventario de Lavandería';

    protected static ?int $navigationSort = 5;

    public ?int $lavanderiaId = null;

    public ?string $activeTab = 'inventario'; // inventario, consumir, reabastecer

    /** @var array<string, mixed>|null */
    public ?array $consumirData = [];

    /** @var array<string, mixed>|null */
    public ?array $reabastecerData = [];

    public function mount(): void
    {
        $lavanderia = Ubicacion::firstOrCreate(
            ['tipo' => 'lavanderia'],
            [
                'nombre' => 'Lavandería Central',
                'descripcion' => 'Bodega virtual para la ropa de cama y blancos en proceso de lavado.',
                'estado' => 1,
            ]
        );
        $this->lavanderiaId = $lavanderia->id;

        $this->form->fill();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Stock::with(['variante.producto', 'lote'])->where('ubicacion_id', $this->lavanderiaId)->where('cantidad', '>', 0))
            ->columns([
                TextColumn::make('variante.producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('variante.nombre_variante')
                    ->label('Variante'),
                TextColumn::make('lote.codigo_lote')
                    ->label('Lote')
                    ->fontFamily('mono'),
                TextColumn::make('cantidad')
                    ->label('Cantidad en Lavandería')
                    ->numeric()
                    ->alignEnd()
                    ->sortable(),
            ]);
    }

    /** @return array<string, Schema> */
    protected function getForms(): array
    {
        return [
            'consumirForm' => $this->makeSchema()
                ->schema([
                    Section::make('Registrar Consumo / Merma de Lavandería')
                        ->description('Registre pérdidas, mermas o consumo directo de insumos dentro de lavandería.')
                        ->columns(2)
                        ->schema([
                            Select::make('stock_id')
                                ->label('Insumo a Descartar / Consumir')
                                ->placeholder('Seleccione insumo')
                                ->options(function () {
                                    return Stock::with(['variante.producto', 'lote'])
                                        ->where('ubicacion_id', $this->lavanderiaId)
                                        ->where('cantidad', '>', 0)
                                        ->get()
                                        ->mapWithKeys(function ($stock) {
                                            $nombre = ($stock->variante->producto->nombre ?? 'Insumo').
                                                         ($stock->variante?->nombre_variante ? " ({$stock->variante->nombre_variante})" : '').
                                                ' [Lote: '.($stock->lote->codigo_lote ?? 'N/A').']'.
                                                " (Disp: {$stock->cantidad})";

                                            return [$stock->id => $nombre];
                                        })
                                        ->toArray();
                                })
                                ->required()
                                ->live()
                                ->native(false)
                                ->afterStateUpdated(function (?int $state, Set $set) {
                                    if ($state) {
                                        $stock = Stock::find($state);
                                        if ($stock) {
                                            $set('max_qty', (float) $stock->cantidad);
                                        }
                                    } else {
                                        $set('max_qty', 0);
                                    }
                                }),

                            TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->maxValue(fn (Get $get) => is_numeric($get('max_qty')) ? (float) $get('max_qty') : 999999.0),

                            Hidden::make('max_qty')
                                ->default(0),

                            TextInput::make('notas')
                                ->label('Notas / Razón de Merma')
                                ->columnSpanFull()
                                ->placeholder('Ej. Sábana rota, mancha irreparable, etc.')
                                ->required(),
                        ]),
                ])
                ->statePath('consumirData'),

            'reabastecerForm' => $this->makeSchema()
                ->schema([
                    Section::make('Reponer Blancos / Insumos a Ubicación')
                        ->description('Traslade blancos limpios desde lavandería hacia habitaciones o bodegas de piso.')
                        ->columns(2)
                        ->schema([
                            Select::make('tipo_destino')
                                ->label('Tipo de Destino')
                                ->options([
                                    'habitacion' => 'Habitación',
                                    'espacio' => 'Espacio Común',
                                    'ubicacion' => 'Bodega / Almacén Físico',
                                ])
                                ->required()
                                ->live()
                                ->native(false),

                            Select::make('destino_id')
                                ->label('Destino Específico')
                                ->placeholder('Seleccione destino')
                                ->options(function (Get $get) {
                                    $tipo = $get('tipo_destino');
                                    if ($tipo === 'habitacion') {
                                        return Habitacion::pluck('nombre', 'id')->toArray();
                                    } elseif ($tipo === 'espacio') {
                                        return Espacio::pluck('nombre', 'id')->toArray();
                                    } elseif ($tipo === 'ubicacion') {
                                        return Ubicacion::whereIn('tipo', ['almacen', 'bodega', 'zona'])->pluck('nombre', 'id')->toArray();
                                    }

                                    return [];
                                })
                                ->required()
                                ->searchable()
                                ->native(false),

                            Repeater::make('items')
                                ->label('Blancos / Insumos a Trasladar')
                                ->columnSpanFull()
                                ->columns(2)
                                ->schema([
                                    Select::make('stock_id')
                                        ->label('Insumo (Lote & Disponible)')
                                        ->placeholder('Seleccione insumo')
                                        ->options(function () {
                                            return Stock::with(['variante.producto', 'lote'])
                                                ->where('ubicacion_id', $this->lavanderiaId)
                                                ->where('cantidad', '>', 0)
                                                ->get()
                                                ->mapWithKeys(function ($stock) {
                                                    $nombre = ($stock->variante->producto->nombre ?? 'Insumo').
                                                        ($stock->variante?->nombre_variante ? " ({$stock->variante->nombre_variante})" : '').
                                                        ' [Lote: '.($stock->lote->codigo_lote ?? 'N/A').']'.
                                                        " (Disp: {$stock->cantidad})";

                                                    return [$stock->id => $nombre];
                                                })
                                                ->toArray();
                                        })
                                        ->required()
                                        ->live()
                                        ->native(false)
                                        ->afterStateUpdated(function (?int $state, Set $set) {
                                            if ($state) {
                                                $stock = Stock::find($state);
                                                if ($stock) {
                                                    $set('max_qty', (float) $stock->cantidad);
                                                    $set('producto_variante_id', $stock->producto_variante_id);
                                                }
                                            } else {
                                                $set('max_qty', 0);
                                                $set('producto_variante_id', null);
                                            }
                                        }),

                                    TextInput::make('cantidad')
                                        ->label('Cantidad')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0.01)
                                        ->maxValue(fn (Get $get) => is_numeric($get('max_qty')) ? (float) $get('max_qty') : 999999.0),

                                    Hidden::make('max_qty')
                                        ->default(0),

                                    Hidden::make('producto_variante_id'),
                                ])
                                ->createItemButtonLabel('Agregar otro insumo'),
                        ]),
                ])
                ->statePath('reabastecerData'),
        ];
    }

    public function submitConsumir(ConsumirStock $consumirStock): void
    {
        $data = $this->consumirForm->getState();
        if (empty($data)) {
            return;
        }
        $stock = isset($data['stock_id']) ? Stock::find($data['stock_id']) : null;

        if (! $stock instanceof Stock) {
            return;
        }

        try {
            $cantidad = isset($data['cantidad']) && is_numeric($data['cantidad']) ? (float) $data['cantidad'] : 0.0;
            $notas = isset($data['notas']) && is_string($data['notas']) ? $data['notas'] : null;

            $consumirStock->execute(
                productoId: (int) $stock->producto_id,
                cantidadRequerida: $cantidad,
                ubicacionId: (int) $this->lavanderiaId,
                tipoMovimiento: 'CONSUMO_LAVANDERIA',
                productoVarianteId: $stock->producto_variante_id ? (int) $stock->producto_variante_id : null,
                creadoPorId: (int) auth()->id(),
                referencia: 'Consumo/Merma registrado en Lavandería',
                notas: $notas
            );

            Notification::make()
                ->title('Consumo Registrado')
                ->body('Se registró el consumo/merma correctamente.')
                ->success()
                ->send();

            $this->consumirForm->fill();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submitReabastecer(ReabastecerUbicacion $reabastecerUbicacion): void
    {
        $data = $this->reabastecerForm->getState();
        if (empty($data)) {
            return;
        }

        $itemsData = $data['items'] ?? [];
        if (! is_array($itemsData) || empty($itemsData)) {
            Notification::make()
                ->title('Error')
                ->body('Debe agregar al menos un insumo para reponer.')
                ->danger()
                ->send();

            return;
        }

        try {
            $items = array_map(function (mixed $item) {
                $itemArr = is_array($item) ? $item : [];

                return ReabastecerItemData::fromArray([
                    'producto_variante_id' => isset($itemArr['producto_variante_id']) && is_numeric($itemArr['producto_variante_id']) ? (int) $itemArr['producto_variante_id'] : 0,
                    'cantidad' => isset($itemArr['cantidad']) && is_numeric($itemArr['cantidad']) ? (float) $itemArr['cantidad'] : 0.0,
                ]);
            }, $itemsData);

            $tipoDestino = isset($data['tipo_destino']) && is_string($data['tipo_destino']) ? $data['tipo_destino'] : '';
            $destinoId = isset($data['destino_id']) && is_numeric($data['destino_id']) ? (int) $data['destino_id'] : 0;

            $reabastecerUbicacion->execute(new ReabastecerUbicacionData(
                tipoDestino: $tipoDestino,
                destinoId: $destinoId,
                items: $items,
                bodegaOrigenId: (int) $this->lavanderiaId,
                creadoPorId: (int) auth()->id(),
                notas: 'Reposición de blancos/insumos desde lavandería.'
            ));

            Notification::make()
                ->title('Reposición Completada')
                ->body('Se reabasteció la ubicación correctamente.')
                ->success()
                ->send();

            $this->reabastecerForm->fill();
            $this->activeTab = 'inventario';
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
