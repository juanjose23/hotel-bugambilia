<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\BusinessLogic\Limpieza\Exceptions\OperacionLimpiezaNoPermitida;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource;
use App\Filament\Shared\Forms\UbicacionLimpiableSelects;
use App\Interactors\Limpieza\Ejecucion\CompletarEjecucionAsignada;
use App\Interactors\Limpieza\Ejecucion\ReclamarEIniciarLimpieza;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Limpieza\Turno;
use App\Repository\Models\User;
use App\Repository\Queries\Colaboradores\ObtenerNombreCompleto;
use App\Repository\Queries\Limpieza\Carrito\ObtenerCarritoAsignado;
use App\Repository\Queries\Limpieza\Carrito\ObtenerCarritosDisponibles;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerEjecucionesConFiltros;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerEjecucionLimpieza;
use App\Repository\Queries\Limpieza\Stock\ObtenerAbastecimientoSugerido;
use App\Repository\Queries\Limpieza\Ubicacion\ObtenerChecklistDefecto;
use App\Repository\Queries\Shared\ObtenerColaboradoresLimpieza;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use UnitEnum;

/**
 * @property Schema $startForm
 * @property Schema $form
 * @property-read int|null $currentColaboradorId
 */
class TableroLimpieza extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected ObtenerNombreCompleto $nombreColaborador;

    protected ObtenerEjecucionLimpieza $obtenerEjecucion;

    protected ObtenerEjecucionesConFiltros $obtenerEjecuciones;

    protected ObtenerCarritosDisponibles $obtenerCarritos;

    protected ObtenerAbastecimientoSugerido $obtenerAbastecimiento;

    protected ObtenerChecklistDefecto $obtenerChecklist;

    public function boot(
        ObtenerNombreCompleto $nombreColaborador,
        ObtenerEjecucionLimpieza $obtenerEjecucion,
        ObtenerEjecucionesConFiltros $obtenerEjecuciones,
        ObtenerCarritosDisponibles $obtenerCarritos,
        ObtenerAbastecimientoSugerido $obtenerAbastecimiento,
        ObtenerChecklistDefecto $obtenerChecklist,
    ): void {
        $this->nombreColaborador = $nombreColaborador;
        $this->obtenerEjecucion = $obtenerEjecucion;
        $this->obtenerEjecuciones = $obtenerEjecuciones;
        $this->obtenerCarritos = $obtenerCarritos;
        $this->obtenerAbastecimiento = $obtenerAbastecimiento;
        $this->obtenerChecklist = $obtenerChecklist;
    }

    protected string $view = 'filament.resources.limpieza.tablero-limpieza';

    protected static ?string $slug = 'tablero-limpieza';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::QueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza';

    protected static ?string $navigationLabel = 'Tablero de Control';

    protected static ?string $title = 'Tablero de Control de Limpieza';

    protected static ?int $navigationSort = 3;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var array<string, mixed>|null */
    public ?array $startData = [];

    public ?int $startingExecutionId = null;

    public ?int $completingExecutionId = null;

    public int $pendientesLimit = 5;

    public int $enProgresoLimit = 5;

    public int $completadasLimit = 5;

    /** @var array<int, array{task: string, completed: bool}> */
    public array $checklist = [];

    public string $observaciones = '';

    /** @var array<int, array{nombre: string, max: float, cantidad: int}> */
    public array $consumos = [];

    /** @return array<string, Schema> */
    protected function getForms(): array
    {
        return [
            'form' => $this->form($this->makeSchema()),
            'startForm' => $this->startForm($this->makeSchema()),
        ];
    }

    public function mount(): void
    {
        $this->form->fill();
        $this->startForm->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make([
                    'default' => 1,
                    'sm' => 3,
                ])
                    ->schema([
                        UbicacionLimpiableSelects::makeTipo('tipo_ubicacion', [
                            'habitacion' => 'Habitación',
                            'espacio' => 'Espacio / Área Común',
                            'ubicacion' => 'Ubicación Física / Bodega',
                        ])
                            ->label('Tipo de Ubicación')
                            ->placeholder('Todos los tipos')
                            ->afterStateUpdated(function (Set $set): void {
                                $set('selectedUbicacionId', null);
                                $set('selectedSubUbicacionId', null);
                            }),

                        UbicacionLimpiableSelects::makeUbicacion('selectedUbicacionId', 'tipo_ubicacion', true)
                            ->label('Ubicación')
                            ->placeholder('Todas las ubicaciones')
                            ->afterStateUpdated(fn (callable $set) => $set('selectedSubUbicacionId', null))
                            ->live(),

                        UbicacionLimpiableSelects::makeSubUbicacion('selectedSubUbicacionId')
                            ->label('Sub-ubicación')
                            ->placeholder('Todas las sub-ubicaciones')
                            ->live(),
                    ]),
            ])
            ->statePath('data');
    }

    public function startForm(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Asignación')
                    ->description('Confirme el responsable y el carrito disponible para esta limpieza.')
                    ->icon('heroicon-o-play-circle')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                Select::make('colaborador_responsable')
                                    ->label('Colaborador responsable')
                                    ->placeholder('Seleccione el colaborador responsable')
                                    ->options(fn (): array => $this->getColaboradoresResponsablesOptions())
                                    ->default(fn (): ?int => $this->currentColaboradorId)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set, mixed $state): void {
                                        if ($state !== null && is_numeric($state)) {
                                            $colabId = (int) $state;
                                            $carritoModel = app(ObtenerCarritoAsignado::class)->execute($colabId);
                                            if ($carritoModel) {
                                                $set('carrito_id', (int) $carritoModel->id);
                                            } elseif ($this->startingExecutionId) {
                                                $availableCarritos = $this->obtenerCarritos->execute($this->startingExecutionId, $colabId);
                                                if (! empty($availableCarritos)) {
                                                    $firstCartKey = array_key_first($availableCarritos);
                                                    $set('carrito_id', (int) $firstCartKey);
                                                } else {
                                                    $set('carrito_id', null);
                                                }
                                            }
                                        }
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false),

                                Select::make('carrito_id')
                                    ->label('Carrito de Limpieza')
                                    ->placeholder('Sin carrito asignado')
                                    ->options(fn (): array => $this->getAvailableCarritos())
                                    ->searchable()
                                    ->preload()
                                    ->native(false),
                            ]),
                    ]),
            ])
            ->statePath('startData');
    }

    /**
     * @return Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function executions(): Collection
    {
        $selectedUbicacionId = $this->data['selectedUbicacionId'] ?? null;
        $selectedSubUbicacionId = $this->data['selectedSubUbicacionId'] ?? null;
        $tipoUbicacion = $this->data['tipo_ubicacion'] ?? null;

        $filtros = [];
        if (is_string($tipoUbicacion) && $tipoUbicacion !== '') {
            $filtros['tipo_ubicacion'] = $tipoUbicacion;
            if (is_numeric($selectedUbicacionId)) {
                $filtros['limpiable_id'] = (int) $selectedUbicacionId;
            }
            if (is_numeric($selectedSubUbicacionId)) {
                $filtros['sub_ubicacion_id'] = (int) $selectedSubUbicacionId;
            }
        }

        return $this->obtenerEjecuciones->execute($filtros);
    }

    /**
     * @return \Illuminate\Support\Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function pendientes(): \Illuminate\Support\Collection
    {
        return $this->executions()
            ->where('estado', EstadoLimpieza::Pendiente)
            ->sortBy('horario.hora_estimada')
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function enProgreso(): \Illuminate\Support\Collection
    {
        return $this->executions()
            ->where('estado', EstadoLimpieza::EnProgreso)
            ->sortBy('hora_inicio')
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function completadas(): \Illuminate\Support\Collection
    {
        return $this->executions()
            ->filter(fn (LimpiezaEjecucion $e): bool => $e->estado->estaFinalizada())
            ->sortByDesc('hora_fin')
            ->values();
    }

    /**
     * Solicitudes de limpieza pendientes sin ejecución asociada.
     *
     * @return Collection<int, SolicitudLimpieza>
     */
    #[Computed]
    public function solicitudes(): Collection
    {
        return SolicitudLimpieza::query()
            ->with(['limpiable', 'personal.persona.colaborador', 'creador', 'ejecuciones'])
            ->where('estado', EstadoLimpieza::Pendiente)
            ->whereDoesntHave('ejecuciones')
            ->orderByRaw("CASE WHEN prioridad = 'alta' THEN 0 WHEN prioridad = 'normal' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function pendientesPaged(): \Illuminate\Support\Collection
    {
        return $this->pendientes()->take($this->pendientesLimit)->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function enProgresoPaged(): \Illuminate\Support\Collection
    {
        return $this->enProgreso()->take($this->enProgresoLimit)->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function completadasPaged(): \Illuminate\Support\Collection
    {
        return $this->completadas()->take($this->completadasLimit)->values();
    }

    #[Computed]
    public function currentColaboradorId(): ?int
    {
        $colaborador = auth()->user()?->persona?->colaborador;

        return $colaborador ? (int) $colaborador->id : null;
    }

    public function obtenerNombreColaborador(LimpiezaEjecucion $ejecucion): string
    {
        $colaborador = $ejecucion->colaborador;
        if (! $colaborador && $ejecucion->solicitud?->personal?->persona?->colaborador) {
            $colaborador = $ejecucion->solicitud->personal->persona->colaborador;
        }

        if (! $colaborador) {
            return 'Sin asignar';
        }

        $nombre = $this->nombreColaborador->obtenerNombreCompleto($colaborador);

        return $nombre !== '' ? $nombre : 'Desconocido';
    }

    public function loadMorePendientes(): void
    {
        $this->pendientesLimit += 5;
    }

    public function loadMoreEnProgreso(): void
    {
        $this->enProgresoLimit += 5;
    }

    public function loadMoreCompletadas(): void
    {
        $this->completadasLimit += 5;
    }

    public function claimAndStart(int $executionId): void
    {
        try {
            $execution = $this->obtenerEjecucion->execute($executionId);
        } catch (ModelNotFoundException) {
            Notification::make()
                ->title('Error')
                ->body('La ejecución de limpieza especificada no existe.')
                ->danger()
                ->send();

            return;
        }

        Gate::authorize('iniciar', $execution);

        $targetColaboradorId = $execution->colaborador_id
            ?: ($execution->solicitud?->personal?->persona?->colaborador?->id ?: $this->currentColaboradorId);

        if (! $targetColaboradorId) {
            $options = $this->getColaboradoresResponsablesOptions();
            if (! empty($options)) {
                $firstKey = array_key_first($options);
                $targetColaboradorId = (int) $firstKey;
            }
        }

        $this->startingExecutionId = $executionId;

        $carritoAsignadoId = $execution->carrito_id;
        if (! $carritoAsignadoId && $targetColaboradorId) {
            $carritoModel = app(ObtenerCarritoAsignado::class)->execute($targetColaboradorId);
            $carritoAsignadoId = $carritoModel?->id;
        }

        if (! $carritoAsignadoId && $targetColaboradorId) {
            $availableCarritos = $this->obtenerCarritos->execute($executionId, $targetColaboradorId);
            if (! empty($availableCarritos)) {
                $firstCartKey = array_key_first($availableCarritos);
                $carritoAsignadoId = (int) $firstCartKey;
            }
        }

        $this->startForm->fill([
            'colaborador_responsable' => $targetColaboradorId,
            'carrito_id' => $carritoAsignadoId,
        ]);

        $this->dispatch('open-modal', id: 'iniciar-limpieza-modal');
    }

    public function closeStartModal(): void
    {
        $this->dispatch('close-modal', id: 'iniciar-limpieza-modal');

        $this->startingExecutionId = null;
        $this->startForm->fill([]);
        $this->startData = [];
    }

    public function confirmStart(ReclamarEIniciarLimpieza $iniciarLimpieza): void
    {
        if (! $this->startingExecutionId) {
            return;
        }

        $execution = $this->obtenerEjecucion->execute($this->startingExecutionId);
        Gate::authorize('iniciar', $execution);

        $startData = $this->startForm->getState();
        $colaboradorId = isset($startData['colaborador_responsable']) && is_numeric($startData['colaborador_responsable'])
            ? (int) $startData['colaborador_responsable']
            : null;

        if ($colaboradorId === null) {
            Notification::make()
                ->title('Seleccione un colaborador')
                ->body('Debe asignar un colaborador responsable para iniciar la limpieza.')
                ->warning()
                ->send();

            return;
        }

        $carritoId = isset($startData['carrito_id']) && is_numeric($startData['carrito_id'])
            ? (int) $startData['carrito_id']
            : null;

        try {
            $execution = $iniciarLimpieza->execute(
                $this->startingExecutionId,
                $colaboradorId,
                $carritoId,
            );

            $nombreLimpiable = match (true) {
                is_object($execution->limpiable) && property_exists($execution->limpiable, 'nombre') && $execution->limpiable->nombre => $execution->limpiable->nombre,
                default => 'Área sin nombre',
            };

            $mensajeCarrito = $carritoId ? ' con el carrito asignado.' : '.';

            Notification::make()
                ->title('Limpieza Iniciada')
                ->body("Has tomado la limpieza de {$nombreLimpiable}{$mensajeCarrito}")
                ->success()
                ->send();

            $this->redirect(LimpiezaEjecucionResource::getUrl('edit', ['record' => $execution->id]));
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al iniciar limpieza')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->closeStartModal();
        }
    }

    /** @return array<int, string> */
    public function getAvailableCarritos(): array
    {
        if (! $this->startingExecutionId) {
            return [];
        }

        $startData = $this->startData ?? [];
        $colaboradorId = isset($startData['colaborador_responsable']) && is_numeric($startData['colaborador_responsable'])
            ? (int) $startData['colaborador_responsable']
            : $this->currentColaboradorId;

        return $this->obtenerCarritos->execute($this->startingExecutionId, $colaboradorId);
    }

    /** @return array<int, string> */
    public function getColaboradorResponsableOption(): array
    {
        $colaborador = auth()->user()?->persona?->colaborador;

        if (! $colaborador) {
            return [];
        }

        $nombre = $this->nombreColaborador->obtenerNombreCompleto($colaborador);

        return [
            (int) $colaborador->id => $nombre !== '' ? $nombre : 'Colaborador actual',
        ];
    }

    /** @return array<int, string> */
    public function getColaboradoresResponsablesOptions(): array
    {
        $opciones = ObtenerColaboradoresLimpieza::opciones();

        $actual = auth()->user()?->persona?->colaborador;
        if ($actual instanceof Colaborador && ! array_key_exists((int) $actual->id, $opciones)) {
            $nombre = $this->nombreColaborador->obtenerNombreCompleto($actual);
            $opciones[(int) $actual->id] = $nombre !== '' ? $nombre : 'Colaborador actual';
        }

        if ($this->startingExecutionId) {
            $ejecucion = LimpiezaEjecucion::query()
                ->with(['colaborador.persona.personaNatural', 'solicitud.personal.persona.colaborador.persona.personaNatural'])
                ->find($this->startingExecutionId);

            $asignado = $ejecucion?->colaborador
                ?: $ejecucion?->solicitud?->personal?->persona?->colaborador;

            if ($asignado instanceof Colaborador && ! array_key_exists((int) $asignado->id, $opciones)) {
                $nombre = $this->nombreColaborador->obtenerNombreCompleto($asignado);
                $opciones[(int) $asignado->id] = $nombre !== '' ? $nombre : "Colaborador #{$asignado->id}";
            }
        }

        return $opciones;
    }

    /** @return array<int, array{nombre: string, cantidad: float, detalles: array<int, string>}> */
    public function getAbastecimientoSugerido(): array
    {
        $colaborador = auth()->user()?->persona?->colaborador;
        if (! $colaborador) {
            return [];
        }

        return $this->obtenerAbastecimiento->execute((int) $colaborador->id);
    }

    public function openCompleteModal(int $executionId): void
    {
        try {
            $execution = $this->obtenerEjecucion->execute($executionId);
        } catch (ModelNotFoundException) {
            return;
        }

        Gate::authorize('completar', $execution);

        $this->completingExecutionId = $executionId;
        $this->observaciones = $execution->observaciones ?? '';

        $existingChecklist = $execution->detalles_checklist;
        if (empty($existingChecklist)) {
            $this->checklist = $this->obtenerChecklist->execute();
        } else {
            $this->checklist = [];
            foreach ($existingChecklist as $task => $status) {
                $this->checklist[] = [
                    'task' => $task,
                    'completed' => (bool) $status,
                ];
            }
        }

        $this->consumos = [];
        if ($execution->carrito_id) {
            $cartStocks = InventarioStock::with(['variante.producto'])
                ->where('ubicacion_id', $execution->carrito_id)
                ->where('cantidad', '>', 0)
                ->get();
            foreach ($cartStocks as $cs) {
                if ($cs->variante) {
                    $this->consumos[$cs->variante->id] = [
                        'nombre' => ($cs->variante->producto->nombre ?? '').($cs->variante->nombre_variante ? " ({$cs->variante->nombre_variante})" : ''),
                        'max' => (float) $cs->cantidad,
                        'cantidad' => 0,
                    ];
                }
            }
        }
    }

    public function closeCompleteModal(): void
    {
        $this->completingExecutionId = null;
        $this->checklist = [];
        $this->observaciones = '';
        $this->consumos = [];
    }

    public function completeExecution(CompletarEjecucionAsignada $terminarLimpieza): void
    {
        if (! $this->completingExecutionId) {
            return;
        }

        $colaborador = auth()->user()?->persona?->colaborador;
        if (! $colaborador) {
            $this->closeCompleteModal();

            return;
        }

        $executionAutorizada = $this->obtenerEjecucion->execute($this->completingExecutionId);
        Gate::authorize('completar', $executionAutorizada);

        $formattedChecklist = [];
        foreach ($this->checklist as $item) {
            $formattedChecklist[$item['task']] = $item['completed'];
        }

        $formattedConsumos = [];
        foreach ($this->consumos as $varianteId => $info) {
            if ((float) $info['cantidad'] > 0) {
                $formattedConsumos[$varianteId] = (float) $info['cantidad'];
            }
        }

        try {
            $execution = $terminarLimpieza->execute(
                $this->completingExecutionId,
                (int) $colaborador->id,
                checklist: $formattedChecklist,
                observaciones: $this->observaciones,
                consumos: $formattedConsumos,
            );
        } catch (OperacionLimpiezaNoPermitida $exception) {
            Notification::make()
                ->title('No se pudo completar la limpieza')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            $this->closeCompleteModal();

            return;
        }
        $estado = $execution->estado;

        $nombreLimpiable = match (true) {
            is_object($execution->limpiable) && property_exists($execution->limpiable, 'nombre') && $execution->limpiable->nombre => $execution->limpiable->nombre,
            default => 'Área sin nombre',
        };

        Notification::make()
            ->title($estado === EstadoLimpieza::Completada ? 'Limpieza Completada' : 'Completada con Discrepancias')
            ->body("Se guardó el registro de limpieza para {$nombreLimpiable}.")
            ->success()
            ->send();

        $this->closeCompleteModal();
    }

    public function asignarSolicitud(int $solicitudId): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            Notification::make()
                ->title('Acceso denegado')
                ->body('Debe iniciar sesión para asignar solicitudes.')
                ->danger()
                ->send();

            return;
        }

        if (! $user->can('page_GestionMesas') && ! $user->can('page_TableroLimpieza') && ! $user->hasRole('super_admin')) {
            Notification::make()
                ->title('Sin permisos')
                ->body('Solo personal autorizado puede asignar solicitudes de limpieza.')
                ->danger()
                ->send();

            return;
        }

        $solicitud = SolicitudLimpieza::query()->with(['limpiable.ubicacion', 'ejecuciones'])->find($solicitudId);

        if (! $solicitud) {
            Notification::make()
                ->title('No encontrada')
                ->body('La solicitud de limpieza ya no existe.')
                ->danger()
                ->send();

            return;
        }

        $colaborador = $user->persona?->colaborador;

        $ejecucionExistente = $solicitud->ejecuciones()->first();
        if (! $ejecucionExistente) {
            $limpiable = $solicitud->limpiable;
            $ubicacion = null;
            if ($limpiable instanceof Model && method_exists($limpiable, 'ubicacion')) {
                $limpiable->loadMissing('ubicacion');
                /** @phpstan-ignore property.notFound */
                $ubicacion = $limpiable->ubicacion;
            }

            $turno = null;
            $currentUbicacion = $ubicacion;
            while ($currentUbicacion) {
                $turno = Turno::where('estado', true)
                    ->whereHas('carritos', fn ($q) => $q->where('ubicacion_id', $currentUbicacion->id))
                    ->first();
                if ($turno) {
                    break;
                }
                $currentUbicacion->loadMissing('padre');
                $currentUbicacion = $currentUbicacion->padre;
            }
            if (! $turno) {
                $turno = Turno::where('estado', true)->first() ?: Turno::first();
            }

            $ejecucionExistente = LimpiezaEjecucion::create([
                'solicitud_id' => $solicitud->id,
                'limpiable_type' => $solicitud->limpiable_type,
                'limpiable_id' => $solicitud->limpiable_id,
                'turno_id' => $turno instanceof Turno ? $turno->id : null,
                'colaborador_id' => $colaborador?->id,
                'fecha' => now()->toDateString(),
                'estado' => EstadoLimpieza::Pendiente,
            ]);
        } elseif ($colaborador instanceof Colaborador && $ejecucionExistente->colaborador_id === null) {
            $ejecucionExistente->update([
                'colaborador_id' => $colaborador->id,
            ]);
        }

        $solicitud->update([
            'personal_id' => $colaborador instanceof Colaborador ? $user->id : $solicitud->personal_id,
            'estado' => EstadoLimpieza::Pendiente,
        ]);

        $nombre = $solicitud->limpiable instanceof Model && property_exists($solicitud->limpiable, 'nombre')
            ? ($solicitud->limpiable->nombre ?? 'Sin nombre')
            : 'Sin nombre';

        Notification::make()
            ->title('Solicitud lista para iniciar')
            ->body("La limpieza de {$nombre} fue enviada al modal de inicio para confirmar colaborador y carrito.")
            ->success()
            ->send();

        unset($this->solicitudes);
        unset($this->executions);
        unset($this->pendientes);

        $targetColaboradorId = $ejecucionExistente->colaborador_id
            ?: ($solicitud->personal?->persona?->colaborador?->id ?: $this->currentColaboradorId);

        if (! $targetColaboradorId) {
            $options = $this->getColaboradoresResponsablesOptions();
            if (! empty($options)) {
                $firstKey = array_key_first($options);
                $targetColaboradorId = (int) $firstKey;
            }
        }

        $this->startingExecutionId = (int) $ejecucionExistente->id;

        $carritoAsignadoId = $ejecucionExistente->carrito_id;
        if (! $carritoAsignadoId && $targetColaboradorId) {
            $carritoModel = app(ObtenerCarritoAsignado::class)->execute($targetColaboradorId);
            $carritoAsignadoId = $carritoModel?->id;
        }

        if (! $carritoAsignadoId && $targetColaboradorId) {
            $availableCarritos = $this->obtenerCarritos->execute((int) $ejecucionExistente->id, $targetColaboradorId);
            if (! empty($availableCarritos)) {
                $firstCartKey = array_key_first($availableCarritos);
                $carritoAsignadoId = (int) $firstCartKey;
            }
        }

        $this->startForm->fill([
            'colaborador_responsable' => $targetColaboradorId,
            'carrito_id' => $carritoAsignadoId,
        ]);

        $this->dispatch('open-modal', id: 'iniciar-limpieza-modal');
    }
}
