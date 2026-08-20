<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\BusinessLogic\Limpieza\Exceptions\OperacionLimpiezaNoPermitida;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource;
use App\Filament\Shared\Forms\UbicacionLimpiableSelects;
use App\Interactors\Limpieza\Ejecucion\AsignarSolicitudLimpieza;
use App\Interactors\Limpieza\Ejecucion\CompletarEjecucionAsignada;
use App\Interactors\Limpieza\Ejecucion\ReclamarEIniciarLimpieza;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\User;
use App\Repository\Queries\Colaboradores\ObtenerNombreCompleto;
use App\Repository\Queries\Limpieza\Carrito\ObtenerCarritoAsignado;
use App\Repository\Queries\Limpieza\Carrito\ObtenerCarritosDisponibles;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerColaboradorAsignadoParaOpciones;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerEjecucionesConFiltros;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerEjecucionLimpieza;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerSolicitudesPendientesSinEjecucion;
use App\Repository\Queries\Limpieza\Stock\ObtenerAbastecimientoSugerido;
use App\Repository\Queries\Limpieza\Stock\ObtenerConsumosDisponiblesCarrito;
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
use Illuminate\Support\Collection as SupportCollection;
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
    use HasPageShield, InteractsWithForms;

    protected ObtenerNombreCompleto $nombreColaborador;

    protected ObtenerEjecucionLimpieza $obtenerEjecucion;

    protected ObtenerEjecucionesConFiltros $obtenerEjecuciones;

    protected ObtenerCarritosDisponibles $obtenerCarritos;

    protected ObtenerAbastecimientoSugerido $obtenerAbastecimiento;

    protected ObtenerChecklistDefecto $obtenerChecklist;

    protected AsignarSolicitudLimpieza $asignarSolicitud;

    protected ObtenerSolicitudesPendientesSinEjecucion $obtenerSolicitudesPendientes;

    protected ObtenerColaboradorAsignadoParaOpciones $obtenerColaboradorAsignado;

    protected ObtenerConsumosDisponiblesCarrito $obtenerConsumosCarrito;

    public function boot(
        ObtenerNombreCompleto $nombreColaborador,
        ObtenerEjecucionLimpieza $obtenerEjecucion,
        ObtenerEjecucionesConFiltros $obtenerEjecuciones,
        ObtenerCarritosDisponibles $obtenerCarritos,
        ObtenerAbastecimientoSugerido $obtenerAbastecimiento,
        ObtenerChecklistDefecto $obtenerChecklist,
        AsignarSolicitudLimpieza $asignarSolicitud,
        ObtenerSolicitudesPendientesSinEjecucion $obtenerSolicitudesPendientes,
        ObtenerColaboradorAsignadoParaOpciones $obtenerColaboradorAsignado,
        ObtenerConsumosDisponiblesCarrito $obtenerConsumosCarrito,
    ): void {
        $this->nombreColaborador = $nombreColaborador;
        $this->obtenerEjecucion = $obtenerEjecucion;
        $this->obtenerEjecuciones = $obtenerEjecuciones;
        $this->obtenerCarritos = $obtenerCarritos;
        $this->obtenerAbastecimiento = $obtenerAbastecimiento;
        $this->obtenerChecklist = $obtenerChecklist;
        $this->asignarSolicitud = $asignarSolicitud;
        $this->obtenerSolicitudesPendientes = $obtenerSolicitudesPendientes;
        $this->obtenerColaboradorAsignado = $obtenerColaboradorAsignado;
        $this->obtenerConsumosCarrito = $obtenerConsumosCarrito;
    }

    protected string $view = 'filament.resources.limpieza.tablero-limpieza';

    protected static ?string $slug = 'tablero-limpieza';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::QueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza & Lavandería';

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
                                    ->afterStateUpdated(function (Set $set, mixed $state): void {
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
     * @return SupportCollection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function pendientes(): SupportCollection
    {
        return $this->executions()
            ->where('estado', EstadoLimpieza::Pendiente)
            ->sortBy('horario.hora_estimada')
            ->values();
    }

    /**
     * @return SupportCollection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function enProgreso(): SupportCollection
    {
        return $this->executions()
            ->where('estado', EstadoLimpieza::EnProgreso)
            ->sortBy('hora_inicio')
            ->values();
    }

    /**
     * @return SupportCollection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function completadas(): SupportCollection
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
        return $this->obtenerSolicitudesPendientes->execute();
    }

    /**
     * @return SupportCollection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function pendientesPaged(): SupportCollection
    {
        return $this->pendientes()->take($this->pendientesLimit)->values();
    }

    /**
     * @return SupportCollection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function enProgresoPaged(): SupportCollection
    {
        return $this->enProgreso()->take($this->enProgresoLimit)->values();
    }

    /**
     * @return SupportCollection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function completadasPaged(): SupportCollection
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

    public function tieneColaboradorAsignado(LimpiezaEjecucion $ejecucion): bool
    {
        return $ejecucion->colaborador !== null
            || $ejecucion->solicitud?->personal?->persona?->colaborador !== null;
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

        $this->startingExecutionId = $executionId;

        $this->prellenarInicio($execution);
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
                auth()->id() !== null ? (int) auth()->id() : null,
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
            $asignado = $this->obtenerColaboradorAsignado->execute($this->startingExecutionId);

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
            $this->consumos = $this->obtenerConsumosCarrito->execute((int) $execution->carrito_id);
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
        $usuarioId = auth()->id() !== null ? (int) auth()->id() : null;

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
                usuarioId: $usuarioId,
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

        try {
            $ejecucion = $this->asignarSolicitud->execute($user, $solicitudId);
        } catch (OperacionLimpiezaNoPermitida $exception) {
            Notification::make()
                ->title('Sin permisos')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        } catch (ModelNotFoundException) {
            Notification::make()
                ->title('No encontrada')
                ->body('La solicitud de limpieza ya no existe.')
                ->danger()
                ->send();

            return;
        }

        $nombre = $ejecucion->limpiable instanceof Model && property_exists($ejecucion->limpiable, 'nombre')
            ? ($ejecucion->limpiable->nombre ?? 'Sin nombre')
            : 'Sin nombre';

        Notification::make()
            ->title('Solicitud lista para iniciar')
            ->body("La limpieza de {$nombre} fue enviada al modal de inicio para confirmar colaborador y carrito.")
            ->success()
            ->send();

        unset($this->solicitudes);
        unset($this->executions);
        unset($this->pendientes);

        $this->startingExecutionId = (int) $ejecucion->id;

        $this->prellenarInicio($ejecucion);
    }

    private function prellenarInicio(LimpiezaEjecucion $ejecucion): void
    {
        $resolucion = $this->resolverColaboradorYCarrito($ejecucion);

        $this->startForm->fill([
            'colaborador_responsable' => $resolucion['colaborador_id'],
            'carrito_id' => $resolucion['carrito_id'],
        ]);

        $this->dispatch('open-modal', id: 'iniciar-limpieza-modal');
    }

    /**
     * @return array{colaborador_id: int|null, carrito_id: int|null}
     */
    private function resolverColaboradorYCarrito(LimpiezaEjecucion $ejecucion): array
    {
        $targetColaboradorId = $ejecucion->colaborador_id
            ?: ($ejecucion->solicitud?->personal?->persona?->colaborador?->id ?: $this->currentColaboradorId);

        if (! $targetColaboradorId) {
            $options = $this->getColaboradoresResponsablesOptions();
            if (! empty($options)) {
                $targetColaboradorId = (int) array_key_first($options);
            }
        }

        $carritoAsignadoId = $ejecucion->carrito_id;
        if (! $carritoAsignadoId && $targetColaboradorId) {
            $carritoModel = app(ObtenerCarritoAsignado::class)->execute($targetColaboradorId);
            $carritoAsignadoId = $carritoModel?->id;
        }

        if (! $carritoAsignadoId && $targetColaboradorId) {
            $availableCarritos = $this->obtenerCarritos->execute((int) $ejecucion->id, $targetColaboradorId);
            if (! empty($availableCarritos)) {
                $carritoAsignadoId = (int) array_key_first($availableCarritos);
            }
        }

        return [
            'colaborador_id' => $targetColaboradorId,
            'carrito_id' => $carritoAsignadoId,
        ];
    }
}
