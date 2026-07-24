<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\BusinessLogic\Limpieza\Exceptions\OperacionLimpiezaNoPermitida;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource;
use App\Filament\Shared\Forms\UbicacionLimpiableSelects;
use App\Interactors\Limpieza\Ejecucion\CompletarEjecucionAsignada;
use App\Interactors\Limpieza\Ejecucion\ReclamarEIniciarLimpieza;
use App\Repository\Models\Inventario\Stock as InventarioStock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Queries\Colaboradores\ObtenerNombreCompleto;
use App\Repository\Queries\Limpieza\Carrito\ObtenerCarritosDisponibles;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerEjecucionesConFiltros;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerEjecucionLimpieza;
use App\Repository\Queries\Limpieza\Stock\ObtenerAbastecimientoSugerido;
use App\Repository\Queries\Limpieza\Ubicacion\ObtenerChecklistDefecto;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use UnitEnum;

/**
 * @property Schema $startForm
 * @property Schema $form
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

    protected string $view = 'filament.pages.limpieza.tablero-limpieza';

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

    public function mount(): void
    {
        $this->form->fill();
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

                        UbicacionLimpiableSelects::makeSubUbicacion('selectedSubUbicacionId', 'selectedUbicacionId', 'tipo_ubicacion')
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
                Select::make('carrito_id')
                    ->label('Carrito de Limpieza')
                    ->placeholder('Sin carrito asignado')
                    ->options(fn (): array => $this->getAvailableCarritos())
                    ->searchable()
                    ->native(false),
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
     * @return Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function pendientes(): Collection
    {
        return $this->executions()
            ->where('estado', EstadoLimpieza::Pendiente)
            ->sortBy('horario.hora_estimada')
            ->values();
    }

    /**
     * @return Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function enProgreso(): Collection
    {
        return $this->executions()
            ->where('estado', EstadoLimpieza::EnProgreso)
            ->sortBy('hora_inicio')
            ->values();
    }

    /**
     * @return Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function completadas(): Collection
    {
        return $this->executions()
            ->filter(fn (LimpiezaEjecucion $e): bool => $e->estado->estaFinalizada())
            ->sortByDesc('hora_fin')
            ->values();
    }

    /**
     * @return Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function pendientesPaged(): Collection
    {
        return $this->pendientes()->take($this->pendientesLimit)->values();
    }

    /**
     * @return Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function enProgresoPaged(): Collection
    {
        return $this->enProgreso()->take($this->enProgresoLimit)->values();
    }

    /**
     * @return Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function completadasPaged(): Collection
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
        $colaborador = auth()->user()?->persona?->colaborador;
        if (! $colaborador) {
            Notification::make()
                ->title('Acceso denegado')
                ->body('Su cuenta de usuario no está asociada a ningún colaborador de limpieza.')
                ->danger()
                ->send();

            return;
        }

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
        $this->startForm->fill();

        $this->dispatch('open-modal', id: 'iniciar-limpieza-modal');
    }

    public function closeStartModal(): void
    {
        $this->dispatch('close-modal', id: 'iniciar-limpieza-modal');

        $this->startingExecutionId = null;
        $this->startData = [];
    }

    public function confirmStart(ReclamarEIniciarLimpieza $iniciarLimpieza): void
    {
        if (! $this->startingExecutionId) {
            return;
        }

        $colaborador = auth()->user()?->persona?->colaborador;

        if (! $colaborador) {
            $this->closeStartModal();

            return;
        }

        $execution = $this->obtenerEjecucion->execute($this->startingExecutionId);
        Gate::authorize('iniciar', $execution);

        $startData = $this->startForm->getState();
        $carritoId = isset($startData['carrito_id']) && is_numeric($startData['carrito_id'])
            ? (int) $startData['carrito_id']
            : null;

        try {
            $execution = $iniciarLimpieza->execute(
                $this->startingExecutionId,
                (int) $colaborador->id,
                $carritoId,
            );

            $nombreLimpiable = match (true) {
                is_object($execution->limpiable) && property_exists($execution->limpiable, 'nombre') && $execution->limpiable->nombre => $execution->limpiable->nombre,
                default => 'Área sin nombre',
            };

            Notification::make()
                ->title('Limpieza Iniciada')
                ->body("Has tomado la limpieza de {$nombreLimpiable} con el carrito asignado.")
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

        return $this->obtenerCarritos->execute($this->startingExecutionId);
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
}
