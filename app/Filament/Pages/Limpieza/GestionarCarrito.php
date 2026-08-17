<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\BusinessLogic\Limpieza\Data\CarritoEstadisticasData;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Interactors\Inventario\TrasladarEntreBodegas;
use App\Interactors\Limpieza\Carrito\AsignarCarritoAEjecucion;
use App\Interactors\Limpieza\Carrito\LiberarCarritoDeEjecucion;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Queries\Limpieza\Carrito\ObtenerEstadisticasCarrito;
use App\Repository\Queries\Limpieza\Stock\ObtenerStockPorUbicacion;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;

/**
 * @property Schema $abastecerForm
 * @property Schema $devolverForm
 * @property Schema $traspasarForm
 * @property Schema $assignForm
 * @property Ubicacion|null $carrito
 * @property bool $bloqueado
 * @property bool $puedeLiberar
 * @property bool $isSuperAdmin
 * @property int $totalItems
 * @property float $totalCantidad
 * @property int $totalMovimientos
 * @property LimpiezaEjecucion|null $ejecucionActiva
 * @property string|null $nombreColaborador
 * @property MovimientoStock|null $ultimoAbastecimiento
 * @property string $ultimoAbastecimientoColaborador
 * @property bool $puedeGestionar
 * @property Collection<int, MovimientoStock> $movimientos
 * @property Collection<int, Stock> $stocks
 */
class GestionarCarrito extends Page implements HasForms, HasTable
{
    use HasPageShield, InteractsWithForms, InteractsWithTable;

    protected ObtenerStockPorUbicacion $stockPorUbicacion;

    protected ObtenerEstadisticasCarrito $estadisticasQuery;

    protected AsignarCarritoAEjecucion $asignarCarrito;

    protected LiberarCarritoDeEjecucion $liberarCarrito;

    public function boot(
        ObtenerStockPorUbicacion $stockPorUbicacion,
        ObtenerEstadisticasCarrito $estadisticasQuery,
        AsignarCarritoAEjecucion $asignarCarrito,
        LiberarCarritoDeEjecucion $liberarCarrito,
    ): void {
        $this->stockPorUbicacion = $stockPorUbicacion;
        $this->estadisticasQuery = $estadisticasQuery;
        $this->asignarCarrito = $asignarCarrito;
        $this->liberarCarrito = $liberarCarrito;
    }

    #[Url(as: 'carrito')]
    public ?int $carritoId = null;

    /** @var array<string, mixed>|null */
    public ?array $abastecerData = [];

    /** @var array<string, mixed>|null */
    public ?array $devolverData = [];

    /** @var array<string, mixed>|null */
    public ?array $traspasarData = [];

    /** @var array<string, mixed>|null */
    public ?array $assignData = [];

    /** @var array<string, mixed>|null */
    public ?array $liberarWarningData = null;

    public string $activeTab = 'abastecer';

    protected string $view = 'filament.resources.limpieza.gestionar-carrito';

    protected static ?string $slug = 'limpieza/gestionar-carrito';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Gestionar Carrito';

    public function mount(): void
    {
        if (! $this->carritoId) {
            $this->redirect(AbastecerCarrito::getUrl());

            return;
        }

        $this->abastecerForm->fill();
        $this->devolverForm->fill();
        $this->traspasarForm->fill();
        $this->assignForm->fill();
    }

    public function getCarritoProperty(): ?Ubicacion
    {
        return $this->carritoId ? Ubicacion::find($this->carritoId) : null;
    }

    protected function getEstadisticasData(): CarritoEstadisticasData
    {
        if (! $this->carritoId) {
            return new CarritoEstadisticasData(
                totalItems: 0,
                totalCantidad: 0.0,
                totalMovimientos: 0,
                bloqueado: false,
                esAsignado: false,
                nombreColaborador: null,
                ejecucionActiva: null,
                ultimoAbastecimiento: null,
            );
        }

        return $this->estadisticasQuery->execute($this->carritoId);
    }

    public function getBloqueadoProperty(): bool
    {
        return $this->getEstadisticasData()->bloqueado;
    }

    public function getPuedeLiberarProperty(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        $superAdmin = config('filament-shield.super_admin.name', 'super_admin');
        $superAdmin = is_string($superAdmin) ? $superAdmin : 'super_admin';

        return $user->hasRole([$superAdmin, 'admin', 'limpieza-supervisor'])
            || $user->can('liberar-carrito');
    }

    public function getIsSuperAdminProperty(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        $superAdmin = config('filament-shield.super_admin.name', 'super_admin');
        $superAdmin = is_string($superAdmin) ? $superAdmin : 'super_admin';

        return $user->hasRole($superAdmin)
            || $user->can('asignar-carrito-limpieza');
    }

    public function getTotalItemsProperty(): int
    {
        return $this->getEstadisticasData()->totalItems;
    }

    public function getTotalCantidadProperty(): float
    {
        return $this->getEstadisticasData()->totalCantidad;
    }

    public function getTotalMovimientosProperty(): int
    {
        return $this->getEstadisticasData()->totalMovimientos;
    }

    public function getEjecucionActivaProperty(): ?LimpiezaEjecucion
    {
        return $this->getEstadisticasData()->ejecucionActiva;
    }

    public function getNombreColaboradorProperty(): ?string
    {
        return $this->getEstadisticasData()->nombreColaborador;
    }

    public function getUltimoAbastecimientoProperty(): ?MovimientoStock
    {
        return $this->getEstadisticasData()->ultimoAbastecimiento;
    }

    public function getUltimoAbastecimientoColaboradorProperty(): string
    {
        $mov = $this->ultimoAbastecimiento;
        if (! $mov || ! $mov->creadoPor) {
            return 'Sistema';
        }

        $persona = $mov->creadoPor->persona;
        if (! $persona) {
            return $mov->creadoPor->name ?? 'Usuario';
        }

        $pn = $persona->personaNatural;

        return trim(
            ($persona->primer_nombre ?? '')
            .' '.($persona->segundo_nombre ?? '')
            .' '.($pn->primer_apellido ?? '')
            .' '.($pn->segundo_apellido ?? '')
        );
    }

    public function getPuedeGestionarProperty(): bool
    {
        $stats = $this->getEstadisticasData();

        return ! $stats->bloqueado || $stats->esAsignado || $this->isSuperAdmin;
    }

    public function getTitle(): string|Htmlable
    {
        return $this->carrito
            ? "Gestionar Carrito: {$this->carrito->nombre}"
            : 'Gestionar Carrito';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Stock::query()
                    ->where('ubicacion_id', $this->carritoId ?? 0)
                    ->where('cantidad', '>', 0)
                    ->with(['variante.producto', 'lote'])
            )
            ->columns([
                TextColumn::make('variante.producto.nombre')
                    ->label('Insumo')
                    ->formatStateUsing(fn (Stock $record): string => ($record->variante?->producto->nombre ?? 'Insumo')
                        .($record->variante?->nombre_variante ? " ({$record->variante->nombre_variante})" : '')
                    )
                    ->searchable(),
                TextColumn::make('lote.codigo_lote')
                    ->label('Lote')
                    ->placeholder('N/A')
                    ->searchable(),
                TextColumn::make('lote.fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date()
                    ->placeholder('N/A'),
                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric(2)
                    ->alignEnd(),
            ]);
    }

    /** @return array<string, Schema> */
    protected function getForms(): array
    {
        return [
            'abastecerForm' => $this->makeSchema()
                ->schema([
                    Select::make('bodega_origen_id')
                        ->label('Bodega / Almacén de Origen')
                        ->placeholder('Seleccione bodega de origen')
                        ->options(fn (): array => Ubicacion::whereIn('tipo', ['almacen', 'bodega'])
                            ->where('nombre', 'not like', 'Carrito%')
                            ->pluck('nombre', 'id')
                            ->toArray())
                        ->required()
                        ->live()
                        ->native(false)
                        ->prefixIcon(Heroicon::Home),

                    Repeater::make('items')
                        ->label('Insumos a Trasladar')
                        ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            Select::make('stock_id')
                                ->label('Seleccionar Insumo (Lote & Disponible)')
                                ->placeholder('Seleccione insumo')
                                ->options(function (Get $get): array {
                                    $origenId = $get('../../bodega_origen_id');
                                    if (! $origenId) {
                                        return [];
                                    }

                                    return $this->stockPorUbicacion->execute(intval(is_scalar($origenId) ? $origenId : 0))
                                        ->with(['variante.producto', 'lote'])
                                        ->get()
                                        ->mapWithKeys(function ($stock): array {
                                            $nombre = ($stock->variante?->producto->nombre ?? 'Insumo')
                                                .($stock->variante?->nombre_variante ? " ({$stock->variante->nombre_variante})" : '')
                                                .' [Lote: '.($stock->lote->codigo_lote ?? 'N/A').']'
                                                ." (Disp: {$stock->cantidad})";

                                            return [$stock->id => $nombre];
                                        })
                                        ->toArray();
                                })
                                ->required()
                                ->live()
                                ->native(false)
                                ->prefixIcon(Heroicon::ListBullet)
                                ->afterStateUpdated(function (?int $state, Set $set): void {
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
                                ->prefixIcon(Heroicon::CheckBadge)
                                ->minValue(0.01)
                                ->maxValue(fn (Get $get): float => is_numeric($get('max_qty')) ? (float) $get('max_qty') : 999999.0),

                            Hidden::make('max_qty')
                                ->default(0),
                        ])
                        ->createItemButtonLabel('Agregar insumo'),
                ])
                ->statePath('abastecerData'),

            'devolverForm' => $this->makeSchema()
                ->schema([
                    Select::make('stock_id')
                        ->label('Insumo en Carrito')
                        ->placeholder('Seleccione insumo')
                        ->options(fn (): array => $this->carritoId ? $this->stockPorUbicacion->execute($this->carritoId)
                            ->with(['variante.producto', 'lote'])
                            ->get()
                            ->mapWithKeys(function ($stock): array {
                                $nombre = ($stock->variante?->producto->nombre ?? 'Insumo')
                                    .($stock->variante?->nombre_variante ? " ({$stock->variante->nombre_variante})" : '')
                                    .' [Lote: '.($stock->lote->codigo_lote ?? 'N/A').']'
                                    ." (Disp: {$stock->cantidad})";

                                return [$stock->id => $nombre];
                            })
                            ->toArray() : []
                        )
                        ->required()
                        ->live()
                        ->native(false)
                        ->afterStateUpdated(function (?int $state, Set $set): void {
                            if ($state) {
                                $stock = Stock::find($state);
                                if ($stock) {
                                    $set('cantidad', $stock->cantidad);
                                    $set('max_qty', $stock->cantidad);
                                }
                            }
                        }),

                    Select::make('bodega_destino_id')
                        ->label('Bodega de Destino')
                        ->placeholder('Seleccione destino')
                        ->options(fn (): array => Ubicacion::whereIn('tipo', ['almacen', 'bodega'])
                            ->where('nombre', 'not like', 'Carrito%')
                            ->pluck('nombre', 'id')
                            ->toArray())
                        ->required()
                        ->native(false),

                    TextInput::make('cantidad')
                        ->label('Cantidad a Devolver')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->maxValue(fn (Get $get): float => is_numeric($get('max_qty')) ? (float) $get('max_qty') : 99999.0),

                    Hidden::make('max_qty')
                        ->default(0),
                ])
                ->statePath('devolverData'),

            'traspasarForm' => $this->makeSchema()
                ->schema([
                    Select::make('stock_id')
                        ->label('Insumo en Carrito')
                        ->placeholder('Seleccione insumo')
                        ->options(fn (): array => $this->carritoId ? $this->stockPorUbicacion->execute($this->carritoId)
                            ->with(['variante.producto', 'lote'])
                            ->get()
                            ->mapWithKeys(function ($stock): array {
                                $nombre = ($stock->variante?->producto->nombre ?? 'Insumo')
                                    .($stock->variante?->nombre_variante ? " ({$stock->variante->nombre_variante})" : '')
                                    .' [Lote: '.($stock->lote->codigo_lote ?? 'N/A').']'
                                    ." (Disp: {$stock->cantidad})";

                                return [$stock->id => $nombre];
                            })
                            ->toArray() : []
                        )
                        ->required()
                        ->live()
                        ->native(false)
                        ->afterStateUpdated(function (?int $state, Set $set): void {
                            if ($state) {
                                $stock = Stock::find($state);
                                if ($stock) {
                                    $set('cantidad', $stock->cantidad);
                                    $set('max_qty', $stock->cantidad);
                                }
                            }
                        }),

                    Select::make('carrito_destino_id')
                        ->label('Carrito de Destino')
                        ->placeholder('Seleccione carrito destino')
                        ->options(fn (): array => Ubicacion::where('nombre', 'like', 'Carrito%')
                            ->where('id', '!=', $this->carritoId)
                            ->pluck('nombre', 'id')
                            ->toArray())
                        ->required()
                        ->native(false),

                    TextInput::make('cantidad')
                        ->label('Cantidad a Traspasar')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->maxValue(fn (Get $get): float => is_numeric($get('max_qty')) ? (float) $get('max_qty') : 99999.0),

                    Hidden::make('max_qty')
                        ->default(0),
                ])
                ->statePath('traspasarData'),

            'assignForm' => $this->makeSchema()
                ->schema([
                    Select::make('limpieza_ejecucion_id')
                        ->label('Tarea de Limpieza Pendiente')
                        ->placeholder('Seleccione una tarea pendiente')
                        ->options(fn (): array => LimpiezaEjecucion::where('estado', EstadoLimpieza::Pendiente)
                            ->whereNull('carrito_id')
                            ->whereDate('fecha', now()->toDateString())
                            ->with(['limpiable', 'colaborador.persona'])
                            ->get()
                            ->mapWithKeys(function ($e): array {
                                $area = $e->limpiable ? (string) ($e->limpiable->nombre ?? $e->limpiable_type) : 'Área';
                                $col = $e->colaborador?->persona ? $e->colaborador->persona->primer_nombre : 'Sin asignación';

                                return [$e->id => "Limpieza #{$e->id} - {$area} ({$col})"];
                            })
                            ->toArray()
                        )
                        ->required()
                        ->native(false),
                ])
                ->statePath('assignData'),
        ];
    }

    public function submitAbastecer(TrasladarEntreBodegas $trasladarEntreBodegas): void
    {
        $data = $this->abastecerForm->getState();
        if (empty($data)) {
            return;
        }
        $origenId = isset($data['bodega_origen_id']) && is_numeric($data['bodega_origen_id']) ? (int) $data['bodega_origen_id'] : 0;
        $destinoId = (int) $this->carritoId;
        $items = $data['items'] ?? [];
        if (! is_array($items)) {
            $items = [];
        }

        if (empty($items)) {
            Notification::make()->title('Error')->body('Debe agregar al menos un insumo.')->danger()->send();

            return;
        }

        try {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $stock = isset($item['stock_id']) ? Stock::find($item['stock_id']) : null;
                if ($stock instanceof Stock) {
                    $cantidad = isset($item['cantidad']) && is_numeric($item['cantidad']) ? (float) $item['cantidad'] : 0.0;
                    $trasladarEntreBodegas->execute(
                        productoId: (int) $stock->producto_id,
                        loteId: (int) $stock->lote_id,
                        cantidad: $cantidad,
                        origenId: $origenId,
                        destinoId: $destinoId,
                        productoVarianteId: $stock->producto_variante_id ? (int) $stock->producto_variante_id : null,
                        creadoPorId: (int) auth()->id(),
                        referencia: "Abastecimiento de Carrito #{$destinoId}",
                        notas: 'Carga de insumos para carrito de limpieza.'
                    );
                }
            }
            Notification::make()->title('Abastecimiento Exitoso')->body('Se cargaron los insumos correctamente al carrito.')->success()->send();

            $this->abastecerForm->fill();
        } catch (\Throwable $e) {
            Notification::make()->title('Error de traslado')->body($e->getMessage())->danger()->send();
        }
    }

    public function submitDevolver(TrasladarEntreBodegas $trasladarEntreBodegas): void
    {
        $data = $this->devolverForm->getState();
        if (empty($data)) {
            return;
        }
        $stock = isset($data['stock_id']) ? Stock::find($data['stock_id']) : null;
        if (! $stock instanceof Stock) {
            return;
        }

        try {
            $cantidad = isset($data['cantidad']) && is_numeric($data['cantidad']) ? (float) $data['cantidad'] : 0.0;
            $destinoId = isset($data['bodega_destino_id']) && is_numeric($data['bodega_destino_id']) ? (int) $data['bodega_destino_id'] : 0;
            $trasladarEntreBodegas->execute(
                productoId: (int) $stock->producto_id,
                loteId: (int) $stock->lote_id,
                cantidad: $cantidad,
                origenId: (int) $this->carritoId,
                destinoId: $destinoId,
                productoVarianteId: $stock->producto_variante_id ? (int) $stock->producto_variante_id : null,
                creadoPorId: (int) auth()->id(),
                referencia: "Devolución de Carrito #{$this->carritoId} a Bodega #{$destinoId}",
                notas: 'Devolución de insumos sobrantes a almacén.'
            );
            Notification::make()->title('Devolución Exitosa')->body('Se devolvió el insumo a la bodega.')->success()->send();
            $this->devolverForm->fill();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function submitTraspasar(TrasladarEntreBodegas $trasladarEntreBodegas): void
    {
        $data = $this->traspasarForm->getState();
        if (empty($data)) {
            return;
        }
        $stock = isset($data['stock_id']) ? Stock::find($data['stock_id']) : null;
        if (! $stock instanceof Stock) {
            return;
        }

        try {
            $cantidad = isset($data['cantidad']) && is_numeric($data['cantidad']) ? (float) $data['cantidad'] : 0.0;
            $destinoId = isset($data['carrito_destino_id']) && is_numeric($data['carrito_destino_id']) ? (int) $data['carrito_destino_id'] : 0;
            $trasladarEntreBodegas->execute(
                productoId: (int) $stock->producto_id,
                loteId: (int) $stock->lote_id,
                cantidad: $cantidad,
                origenId: (int) $this->carritoId,
                destinoId: $destinoId,
                productoVarianteId: $stock->producto_variante_id ? (int) $stock->producto_variante_id : null,
                creadoPorId: (int) auth()->id(),
                referencia: "Traspaso de Carrito #{$this->carritoId} a Carrito #{$destinoId}",
                notas: 'Traspaso de insumos entre carritos de limpieza.'
            );
            Notification::make()->title('Traspaso Exitoso')->body('Se traspasó el insumo al otro carrito.')->success()->send();
            $this->traspasarForm->fill();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function prepararLiberacion(): void
    {
        $ejecucion = $this->ejecucionActiva;
        if (! $ejecucion) {
            $this->liberarWarningData = null;
            $this->dispatch('open-modal', id: 'confirm-liberar-modal');

            return;
        }

        $colaboradorNombre = $this->nombreColaborador ?? 'Sin asignar';
        $areaNombre = $ejecucion->limpiable ? (string) ($ejecucion->limpiable->nombre ?? $ejecucion->limpiable_type) : 'Área no especificada';

        $this->liberarWarningData = [
            'is_active' => $ejecucion->estado === EstadoLimpieza::EnProgreso,
            'ejecucion_id' => $ejecucion->id,
            'area' => $areaNombre,
            'colaborador' => $colaboradorNombre,
            'estado' => $ejecucion->estado->getLabel(),
        ];

        $this->dispatch('open-modal', id: 'confirm-liberar-modal');
    }

    public function liberarCarrito(): void
    {
        if (! $this->puedeLiberar) {
            Notification::make()
                ->title('Sin permisos')
                ->body('No tiene permisos para liberar este carrito.')
                ->danger()
                ->send();

            return;
        }

        $ejecucion = $this->ejecucionActiva;
        if ($ejecucion) {
            $this->liberarCarrito->execute($ejecucion);
        }

        $this->dispatch('close-modal', id: 'confirm-liberar-modal');
        $this->liberarWarningData = null;

        Notification::make()
            ->title('Carrito Liberado')
            ->body('El carrito ha sido liberado exitosamente.')
            ->success()
            ->send();
    }

    public function openAssignModal(): void
    {
        $this->assignForm->fill();
        $this->dispatch('open-modal', id: 'assign-carrito-modal');
    }

    public function closeAssignModal(): void
    {
        $this->dispatch('close-modal', id: 'assign-carrito-modal');
    }

    public function confirmAssign(): void
    {
        $data = $this->assignForm->getState();
        $rawId = $data['limpieza_ejecucion_id'] ?? null;
        $ejecucionId = is_numeric($rawId) ? (int) $rawId : 0;
        if ($ejecucionId > 0 && $this->carritoId) {
            $ejecucion = $this->asignarCarrito->execute($ejecucionId, $this->carritoId);

            Notification::make()
                ->title('Carrito Asignado')
                ->body("El carrito fue asignado a la tarea de limpieza #{$ejecucion->id}.")
                ->success()
                ->send();
        }

        $this->closeAssignModal();
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocksProperty(): Collection
    {
        return $this->carritoId
            ? $this->stockPorUbicacion->execute($this->carritoId)
                ->with(['variante.producto', 'lote'])
                ->get()
            : new Collection;
    }

    /**
     * @return Collection<int, MovimientoStock>
     */
    public function getMovimientosProperty(): Collection
    {
        if (! $this->carritoId) {
            return new Collection;
        }

        return MovimientoStock::with(['producto', 'lote', 'ubicacionOrigen', 'ubicacionDestino', 'creadoPor.persona'])
            ->where(function ($q) {
                $q->where('ubicacion_origen_id', $this->carritoId)
                    ->orWhere('ubicacion_destino_id', $this->carritoId);
            })
            ->latest()
            ->take(15)
            ->get();
    }
}
