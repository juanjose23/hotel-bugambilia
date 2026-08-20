<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\BusinessLogic\Limpieza\Data\CarritoEstadisticasData;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Filament\Shared\Forms\Limpieza\BodegaLimpiezaSelect;
use App\Filament\Shared\Forms\Limpieza\StockUbicacionSelect;
use App\Interactors\Limpieza\Carrito\AsignarCarritoAEjecucion;
use App\Interactors\Limpieza\Carrito\LiberarCarritoDeEjecucion;
use App\Interactors\Limpieza\Carrito\RegistrarSalidaCarrito;
use App\Interactors\Limpieza\Carrito\TrasladarStockSeleccionadoCarrito;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Queries\Limpieza\Carrito\ObtenerCarritoPorId;
use App\Repository\Queries\Limpieza\Carrito\ObtenerEstadisticasCarrito;
use App\Repository\Queries\Limpieza\Carrito\ObtenerInventarioCarrito;
use App\Repository\Queries\Limpieza\Carrito\ObtenerMovimientosRecientesCarrito;
use App\Repository\Queries\Limpieza\Carrito\ObtenerOpcionesCarritosDestino;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerOpcionesEjecucionesPendientesCarrito;
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
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
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
 * @property Schema $usoForm
 * @property Schema $mermaForm
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

    protected ObtenerMovimientosRecientesCarrito $movimientosRecientesCarrito;

    protected ObtenerInventarioCarrito $inventarioCarrito;

    protected ObtenerOpcionesCarritosDestino $opcionesCarritosDestino;

    protected ObtenerOpcionesEjecucionesPendientesCarrito $opcionesEjecucionesPendientes;

    protected ?ObtenerCarritoPorId $obtenerCarritoPorId = null;

    public function boot(
        ObtenerStockPorUbicacion $stockPorUbicacion,
        ObtenerEstadisticasCarrito $estadisticasQuery,
        AsignarCarritoAEjecucion $asignarCarrito,
        LiberarCarritoDeEjecucion $liberarCarrito,
        ObtenerMovimientosRecientesCarrito $movimientosRecientesCarrito,
        ObtenerInventarioCarrito $inventarioCarrito,
        ObtenerOpcionesCarritosDestino $opcionesCarritosDestino,
        ObtenerOpcionesEjecucionesPendientesCarrito $opcionesEjecucionesPendientes,
        ObtenerCarritoPorId $obtenerCarritoPorId,
    ): void {
        $this->stockPorUbicacion = $stockPorUbicacion;
        $this->estadisticasQuery = $estadisticasQuery;
        $this->asignarCarrito = $asignarCarrito;
        $this->liberarCarrito = $liberarCarrito;
        $this->movimientosRecientesCarrito = $movimientosRecientesCarrito;
        $this->inventarioCarrito = $inventarioCarrito;
        $this->opcionesCarritosDestino = $opcionesCarritosDestino;
        $this->opcionesEjecucionesPendientes = $opcionesEjecucionesPendientes;
        $this->obtenerCarritoPorId = $obtenerCarritoPorId;
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
    public ?array $usoData = [];

    /** @var array<string, mixed>|null */
    public ?array $mermaData = [];

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
        $this->usoForm->fill();
        $this->mermaForm->fill();
        $this->assignForm->fill();
    }

    public function getCarritoProperty(): ?Ubicacion
    {
        return app(ObtenerCarritoPorId::class)->execute($this->carritoId);
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

        $colaboradorId = auth()->user()?->persona?->colaborador?->id;

        return $this->estadisticasQuery->execute(
            $this->carritoId,
            is_numeric($colaboradorId) ? (int) $colaboradorId : null,
        );
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
        return 'Gestionar Carrito';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->inventarioCarrito->execute($this->carritoId ?? 0))
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
                    BodegaLimpiezaSelect::make('bodega_origen_id', 'Bodega / Almacén de Origen')
                        ->live()
                        ->prefixIcon(Heroicon::Home),

                    Repeater::make('items')
                        ->label('Insumos a Trasladar')
                        ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            StockUbicacionSelect::make(
                                column: 'stock_id',
                                label: 'Seleccionar Insumo (Lote & Disponible)',
                                ubicacionId: fn (Get $get): mixed => $get('../../bodega_origen_id'),
                            ),

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
                        ->addActionLabel('Agregar insumo'),
                ])
                ->statePath('abastecerData'),

            'devolverForm' => $this->makeSchema()
                ->schema([
                    StockUbicacionSelect::make(
                        column: 'stock_id',
                        label: 'Insumo en Carrito',
                        ubicacionId: fn (Get $get): int => (int) $this->carritoId,
                        llenarCantidad: true,
                    ),

                    BodegaLimpiezaSelect::make('bodega_destino_id', 'Bodega de Destino'),

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
                    StockUbicacionSelect::make(
                        column: 'stock_id',
                        label: 'Insumo en Carrito',
                        ubicacionId: fn (Get $get): int => (int) $this->carritoId,
                        llenarCantidad: true,
                    ),

                    Select::make('carrito_destino_id')
                        ->label('Carrito de Destino')
                        ->placeholder('Seleccione carrito destino')
                        ->options(fn (): array => $this->opcionesCarritosDestino->execute($this->carritoId))
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

            'usoForm' => $this->makeSchema()
                ->schema($this->stockSalidaCarritoSchema(
                    cantidadLabel: 'Cantidad Utilizada',
                    notasLabel: 'Detalle de Uso',
                    notasPlaceholder: 'Ej. Reposición en habitación, kit dejado en baño, cambio de toallas...'
                ))
                ->statePath('usoData'),

            'mermaForm' => $this->makeSchema()
                ->schema($this->stockSalidaCarritoSchema(
                    cantidadLabel: 'Cantidad en Merma',
                    notasLabel: 'Motivo de Merma',
                    notasPlaceholder: 'Ej. Producto derramado, dañado, contaminado, roto...'
                ))
                ->statePath('mermaData'),

            'assignForm' => $this->makeSchema()
                ->schema([
                    Select::make('limpieza_ejecucion_id')
                        ->label('Tarea de Limpieza Pendiente')
                        ->placeholder('Seleccione una tarea pendiente')
                        ->options(fn (): array => $this->opcionesEjecucionesPendientes->execute())
                        ->required()
                        ->native(false),
                ])
                ->statePath('assignData'),
        ];
    }

    /** @return array<int, Component> */
    private function stockSalidaCarritoSchema(string $cantidadLabel, string $notasLabel, string $notasPlaceholder): array
    {
        return [
            StockUbicacionSelect::make(
                column: 'stock_id',
                label: 'Insumo en Carrito',
                ubicacionId: fn (Get $get): int => (int) $this->carritoId,
            ),

            TextInput::make('cantidad')
                ->label($cantidadLabel)
                ->numeric()
                ->required()
                ->minValue(0.01)
                ->maxValue(fn (Get $get): float => is_numeric($get('max_qty')) ? (float) $get('max_qty') : 99999.0),

            TextInput::make('notas')
                ->label($notasLabel)
                ->placeholder($notasPlaceholder)
                ->columnSpanFull(),

            Hidden::make('max_qty')
                ->default(0),
        ];
    }

    public function submitAbastecer(TrasladarStockSeleccionadoCarrito $trasladarStock): void
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
            $userId = auth()->id();

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $stockId = isset($item['stock_id']) && is_numeric($item['stock_id']) ? (int) $item['stock_id'] : 0;
                $cantidad = isset($item['cantidad']) && is_numeric($item['cantidad']) ? (float) $item['cantidad'] : 0.0;

                $trasladarStock->execute(
                    stockId: $stockId,
                    cantidad: $cantidad,
                    origenId: $origenId,
                    destinoId: $destinoId,
                    creadoPorId: $userId !== null ? (int) $userId : null,
                    referencia: "Abastecimiento de Carrito #{$destinoId}",
                    notas: 'Carga de insumos para carrito de limpieza.',
                );
            }
            Notification::make()->title('Abastecimiento Exitoso')->body('Se cargaron los insumos correctamente al carrito.')->success()->send();

            $this->abastecerForm->fill();
        } catch (\Throwable $e) {
            Notification::make()->title('Error de traslado')->body($e->getMessage())->danger()->send();
        }
    }

    public function submitDevolver(TrasladarStockSeleccionadoCarrito $trasladarStock): void
    {
        $data = $this->devolverForm->getState();
        if (empty($data)) {
            return;
        }
        try {
            $stockId = isset($data['stock_id']) && is_numeric($data['stock_id']) ? (int) $data['stock_id'] : 0;
            $cantidad = isset($data['cantidad']) && is_numeric($data['cantidad']) ? (float) $data['cantidad'] : 0.0;
            $destinoId = isset($data['bodega_destino_id']) && is_numeric($data['bodega_destino_id']) ? (int) $data['bodega_destino_id'] : 0;
            $userId = auth()->id();

            $trasladarStock->execute(
                stockId: $stockId,
                cantidad: $cantidad,
                origenId: (int) $this->carritoId,
                destinoId: $destinoId,
                creadoPorId: $userId !== null ? (int) $userId : null,
                referencia: "Devolución de Carrito #{$this->carritoId} a Bodega #{$destinoId}",
                notas: 'Devolución de insumos sobrantes a almacén.',
            );
            Notification::make()->title('Devolución Exitosa')->body('Se devolvió el insumo a la bodega.')->success()->send();
            $this->devolverForm->fill();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function submitTraspasar(TrasladarStockSeleccionadoCarrito $trasladarStock): void
    {
        $data = $this->traspasarForm->getState();
        if (empty($data)) {
            return;
        }
        try {
            $stockId = isset($data['stock_id']) && is_numeric($data['stock_id']) ? (int) $data['stock_id'] : 0;
            $cantidad = isset($data['cantidad']) && is_numeric($data['cantidad']) ? (float) $data['cantidad'] : 0.0;
            $destinoId = isset($data['carrito_destino_id']) && is_numeric($data['carrito_destino_id']) ? (int) $data['carrito_destino_id'] : 0;
            $userId = auth()->id();

            $trasladarStock->execute(
                stockId: $stockId,
                cantidad: $cantidad,
                origenId: (int) $this->carritoId,
                destinoId: $destinoId,
                creadoPorId: $userId !== null ? (int) $userId : null,
                referencia: "Traspaso de Carrito #{$this->carritoId} a Carrito #{$destinoId}",
                notas: 'Traspaso de insumos entre carritos de limpieza.',
            );
            Notification::make()->title('Traspaso Exitoso')->body('Se traspasó el insumo al otro carrito.')->success()->send();
            $this->traspasarForm->fill();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function submitUso(RegistrarSalidaCarrito $registrarSalidaCarrito): void
    {
        $this->registrarSalidaDesdeFormulario(
            formState: $this->usoForm->getState(),
            tipoSalida: 'uso',
            registrarSalidaCarrito: $registrarSalidaCarrito,
            successTitle: 'Uso Registrado',
            successBody: 'Se registró el insumo utilizado en la limpieza.',
            resetForm: fn (): mixed => $this->usoForm->fill(),
        );
    }

    public function submitMerma(RegistrarSalidaCarrito $registrarSalidaCarrito): void
    {
        $this->registrarSalidaDesdeFormulario(
            formState: $this->mermaForm->getState(),
            tipoSalida: 'merma',
            registrarSalidaCarrito: $registrarSalidaCarrito,
            successTitle: 'Merma Registrada',
            successBody: 'Se registró la merma del carrito correctamente.',
            resetForm: fn (): mixed => $this->mermaForm->fill(),
        );
    }

    /**
     * @param  array<string, mixed>  $formState
     */
    private function registrarSalidaDesdeFormulario(
        array $formState,
        string $tipoSalida,
        RegistrarSalidaCarrito $registrarSalidaCarrito,
        string $successTitle,
        string $successBody,
        \Closure $resetForm,
    ): void {
        if (empty($formState) || ! $this->carritoId) {
            return;
        }

        try {
            $stockId = isset($formState['stock_id']) && is_numeric($formState['stock_id']) ? (int) $formState['stock_id'] : 0;
            $cantidad = isset($formState['cantidad']) && is_numeric($formState['cantidad']) ? (float) $formState['cantidad'] : 0.0;
            $notas = isset($formState['notas']) && is_string($formState['notas']) ? $formState['notas'] : null;
            $userId = auth()->id();

            $registrarSalidaCarrito->execute(
                stockId: $stockId,
                cantidad: $cantidad,
                carritoId: (int) $this->carritoId,
                tipoSalida: $tipoSalida,
                ejecucionId: $this->ejecucionActiva?->id,
                creadoPorId: $userId !== null ? (int) $userId : null,
                notas: $notas,
            );

            Notification::make()
                ->title($successTitle)
                ->body($successBody)
                ->success()
                ->send();

            $resetForm();
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

        return $this->movimientosRecientesCarrito->execute($this->carritoId);
    }
}
