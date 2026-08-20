<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\Filament\Shared\Forms\Limpieza\DestinoLavanderiaSelects;
use App\Filament\Shared\Forms\Limpieza\StockUbicacionSelect;
use App\Interactors\Limpieza\Lavanderia\RegistrarConsumoMermaLavanderia;
use App\Interactors\Limpieza\Lavanderia\RegistrarEntradaDirectaLavanderia;
use App\Interactors\Limpieza\Lavanderia\ReponerDesdeLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerInventarioLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerOpcionesBlancosLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ObtenerUbicacionesInventarioLavanderia;
use App\Repository\Queries\Limpieza\Lavanderia\ResolverUbicacionLavanderia;
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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

/**
 * @property Schema $form
 * @property Schema $entradaForm
 * @property Schema $consumirForm
 * @property Schema $reabastecerForm
 */
class ControlLavanderia extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    protected ResolverUbicacionLavanderia $resolverLavanderia;

    protected ObtenerInventarioLavanderia $inventarioLavanderia;

    protected ObtenerOpcionesBlancosLavanderia $opcionesBlancos;

    protected ObtenerUbicacionesInventarioLavanderia $ubicacionesInventarioLavanderia;

    public function boot(
        ResolverUbicacionLavanderia $resolverLavanderia,
        ObtenerInventarioLavanderia $inventarioLavanderia,
        ObtenerOpcionesBlancosLavanderia $opcionesBlancos,
        ObtenerUbicacionesInventarioLavanderia $ubicacionesInventarioLavanderia,
    ): void {
        $this->resolverLavanderia = $resolverLavanderia;
        $this->inventarioLavanderia = $inventarioLavanderia;
        $this->opcionesBlancos = $opcionesBlancos;
        $this->ubicacionesInventarioLavanderia = $ubicacionesInventarioLavanderia;
    }

    protected string $view = 'filament.resources.limpieza.control-lavanderia';

    protected static ?string $slug = 'limpieza/control-lavanderia';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza & Lavandería';

    protected static ?string $navigationLabel = 'Control de Lavandería';

    protected static ?string $title = 'Control de Inventario de Lavandería';

    protected static ?int $navigationSort = 5;

    public ?int $lavanderiaId = null;

    public ?string $activeTab = 'inventario'; // inventario, entrada, consumir, reabastecer

    /** @var array<string, mixed>|null */
    public ?array $entradaData = [];

    /** @var array<string, mixed>|null */
    public ?array $consumirData = [];

    /** @var array<string, mixed>|null */
    public ?array $reabastecerData = [];

    public function mount(): void
    {
        $this->lavanderiaId = $this->resolverLavanderia->execute()->id;

        $this->entradaForm->fill();
        $this->consumirForm->fill();
        $this->reabastecerForm->fill();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->inventarioLavanderia->execute($this->ubicacionesInventarioLavanderia->execute()))
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
            'entradaForm' => $this->makeSchema()
                ->schema([
                    Section::make('Entrada directa a lavandería')
                        ->description('Registre blancos o piezas sueltas que entran a lavandería sin depender de una habitación, espacio o bodega origen.')
                        ->columns(2)
                        ->schema([
                            Select::make('producto_variante_id')
                                ->label('Blanco / Producto')
                                ->placeholder('Seleccione el blanco')
                                ->options(fn (): array => $this->opcionesBlancos->execute())
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false),

                            TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->required()
                                ->minValue(0.01),

                            TextInput::make('notas')
                                ->label('Notas')
                                ->placeholder('Ej. Cortinas retiradas para lavado, mantel de evento, pieza suelta...')
                                ->columnSpanFull(),
                        ]),
                ])
                ->statePath('entradaData'),

            'consumirForm' => $this->makeSchema()
                ->schema([
                    Section::make('Registrar Consumo / Merma de Lavandería')
                        ->description('Registre pérdidas, mermas o consumo directo de insumos dentro de lavandería.')
                        ->columns(2)
                        ->schema([
                            StockUbicacionSelect::make(
                                column: 'stock_id',
                                label: 'Insumo a Descartar / Consumir',
                                ubicacionId: fn (Get $get): array => $this->ubicacionesInventarioLavanderia->execute(),
                            ),

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
                            DestinoLavanderiaSelects::tipo(),

                            DestinoLavanderiaSelects::destino(),

                            Repeater::make('items')
                                ->label('Blancos / Insumos a Trasladar')
                                ->columnSpanFull()
                                ->columns(2)
                                ->schema([
                                    StockUbicacionSelect::make(
                                        column: 'stock_id',
                                        label: 'Insumo (Lote & Disponible)',
                                        ubicacionId: fn (Get $get): array => $this->ubicacionesInventarioLavanderia->execute(incluirSucios: false),
                                    ),

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
                                ->addActionLabel('Agregar otro insumo'),
                        ]),
                ])
                ->statePath('reabastecerData'),
        ];
    }

    public function submitEntrada(RegistrarEntradaDirectaLavanderia $registrarEntrada): void
    {
        $data = $this->entradaForm->getState();
        if (empty($data)) {
            return;
        }

        try {
            $productoVarianteId = isset($data['producto_variante_id']) && is_numeric($data['producto_variante_id'])
                ? (int) $data['producto_variante_id']
                : 0;
            $cantidad = isset($data['cantidad']) && is_numeric($data['cantidad'])
                ? (float) $data['cantidad']
                : 0.0;
            $notas = isset($data['notas']) && is_string($data['notas']) ? $data['notas'] : null;

            $userId = auth()->id();

            $registrarEntrada->execute(
                productoVarianteId: $productoVarianteId,
                cantidad: $cantidad,
                ubicacionLavanderiaId: (int) $this->lavanderiaId,
                creadoPorId: $userId !== null ? (int) $userId : null,
                notas: $notas,
            );

            Notification::make()
                ->title('Entrada registrada')
                ->body('El blanco fue ingresado a lavandería correctamente.')
                ->success()
                ->send();

            $this->entradaForm->fill();
            $this->activeTab = 'inventario';
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submitConsumir(RegistrarConsumoMermaLavanderia $registrarConsumo): void
    {
        $data = $this->consumirForm->getState();
        if (empty($data)) {
            return;
        }

        try {
            $stockId = isset($data['stock_id']) && is_numeric($data['stock_id']) ? (int) $data['stock_id'] : 0;
            $cantidad = isset($data['cantidad']) && is_numeric($data['cantidad']) ? (float) $data['cantidad'] : 0.0;
            $notas = isset($data['notas']) && is_string($data['notas']) ? $data['notas'] : null;
            $userId = auth()->id();

            $registrarConsumo->execute(
                stockId: $stockId,
                cantidad: $cantidad,
                lavanderiaId: $this->ubicacionesInventarioLavanderia->execute(),
                creadoPorId: $userId !== null ? (int) $userId : null,
                notas: $notas,
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

    public function submitReabastecer(ReponerDesdeLavanderia $reponerDesdeLavanderia): void
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
            $tipoDestino = isset($data['tipo_destino']) && is_string($data['tipo_destino']) ? $data['tipo_destino'] : '';
            $destinoId = isset($data['destino_id']) && is_numeric($data['destino_id']) ? (int) $data['destino_id'] : 0;

            $userId = auth()->id();

            foreach ($itemsData as $item) {
                $itemArr = is_array($item) ? $item : [];
                $stockId = isset($itemArr['stock_id']) && is_numeric($itemArr['stock_id']) ? (int) $itemArr['stock_id'] : 0;
                $cantidad = isset($itemArr['cantidad']) && is_numeric($itemArr['cantidad']) ? (float) $itemArr['cantidad'] : 0.0;

                $reponerDesdeLavanderia->execute(
                    stockId: $stockId,
                    cantidad: $cantidad,
                    ubicacionLavanderiaId: $this->ubicacionesInventarioLavanderia->execute(incluirSucios: false),
                    tipoDestino: $tipoDestino,
                    destinoId: $destinoId,
                    creadoPorId: $userId !== null ? (int) $userId : null,
                );
            }

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
