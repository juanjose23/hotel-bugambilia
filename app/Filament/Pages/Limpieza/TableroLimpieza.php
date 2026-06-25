<?php

declare(strict_types=1);

namespace App\Filament\Pages\Limpieza;

use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Inventario\Stock as InventarioStock;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\Models\Shared\Stock;
use App\UseCases\Limpieza\Mutations\IniciarLimpieza;
use App\UseCases\Limpieza\Mutations\TerminarLimpieza;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use UnitEnum;

/**
 * @property Schema $form
 * @property array<string, int> $ubicaciones
 */
class TableroLimpieza extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.limpieza.tablero-limpieza';

    protected static ?string $slug = 'tablero-limpieza';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::QueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Limpieza';

    protected static ?string $navigationLabel = 'Tablero de Control';

    protected static ?string $title = 'Tablero de Control de Limpieza';

    protected static ?int $navigationSort = 3;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public ?int $startingExecutionId = null;

    public ?int $selectedCarritoId = null;

    public ?int $completingExecutionId = null;

    /** @var array<int, array{task: string, completed: bool}> */
    public array $checklist = [];

    public string $observaciones = '';

    /** @var array<int, array{nombre: string, max: float, cantidad: int}> */
    public array $consumos = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('selectedUbicacionId')
                    ->label('Ubicación')
                    ->placeholder('Todas las ubicaciones')
                    ->options(fn () => $this->ubicaciones)
                    ->searchable()
                    ->native(false)
                    ->live(),
            ])
            ->statePath('data');
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function ubicaciones(): array
    {
        $all = Ubicacion::all();
        $map = $all->keyBy('id');

        $buildPath = function (Ubicacion $u) use (&$buildPath, $map): string {
            if ($u->padre_id && $map->has($u->padre_id)) {
                /** @var Ubicacion $padre */
                $padre = $map->get($u->padre_id);

                return $buildPath($padre).' ➔ '.$u->nombre;
            }

            return $u->nombre;
        };

        $result = [];
        foreach ($all as $u) {
            $result[$u->id] = $buildPath($u);
        }

        asort($result);

        return $result;
    }

    /**
     * @return Collection<int, LimpiezaEjecucion>
     */
    #[Computed]
    public function executions(): Collection
    {
        $query = LimpiezaEjecucion::whereDate('fecha', now()->toDateString())
            ->with(['limpiable', 'colaborador.persona.personaNatural', 'colaborador.persona.personaJuridica', 'horario']);

        $selectedUbicacionId = $this->data['selectedUbicacionId'] ?? null;

        if ($selectedUbicacionId) {
            $ubicacionIdInt = is_scalar($selectedUbicacionId) ? (int) $selectedUbicacionId : 0;
            $ubicacionIds = Ubicacion::obtenerDescendientesIds($ubicacionIdInt);
            $query->where(function ($q) use ($ubicacionIds) {
                $q->where(function ($sub) use ($ubicacionIds) {
                    $sub->where('limpiable_type', Ubicacion::class)
                        ->whereIn('limpiable_id', $ubicacionIds);
                })->orWhere(function ($sub) use ($ubicacionIds) {
                    $sub->where('limpiable_type', Habitacion::class)
                        ->whereIn('limpiable_id', function ($subQuery) use ($ubicacionIds) {
                            $subQuery->select('id')
                                ->from('habitaciones')
                                ->whereIn('ubicacion_id', $ubicacionIds);
                        });
                })->orWhere(function ($sub) use ($ubicacionIds) {
                    $sub->where('limpiable_type', Espacio::class)
                        ->whereIn('limpiable_id', function ($subQuery) use ($ubicacionIds) {
                            $subQuery->select('id')
                                ->from('espacios')
                                ->whereIn('ubicacion_id', $ubicacionIds);
                        });
                });
            });
        }

        return $query->get();
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

        $execution = LimpiezaEjecucion::find($executionId);
        if (! $execution) {
            Notification::make()
                ->title('Error')
                ->body('La ejecución de limpieza especificada no existe.')
                ->danger()
                ->send();

            return;
        }

        $this->startingExecutionId = $executionId;
        $this->selectedCarritoId = null;
    }

    public function closeStartModal(): void
    {
        $this->startingExecutionId = null;
        $this->selectedCarritoId = null;
    }

    public function confirmStart(IniciarLimpieza $iniciarLimpieza): void
    {
        if (! $this->startingExecutionId) {
            return;
        }

        $execution = LimpiezaEjecucion::find($this->startingExecutionId);
        $colaborador = auth()->user()?->persona?->colaborador;

        if (! $execution || ! $colaborador) {
            $this->closeStartModal();

            return;
        }

        try {
            $iniciarLimpieza->execute($execution, $colaborador->id, $this->selectedCarritoId ? (int) $this->selectedCarritoId : null);

            /** @var mixed $limpiable */
            $limpiable = $execution->limpiable;
            $nombreLimpiable = is_object($limpiable) ? ($limpiable->nombre ?? 'Área sin nombre') : 'Área sin nombre';

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
        $execution = LimpiezaEjecucion::with('turno')->find($this->startingExecutionId);
        if (! $execution) {
            return [];
        }
        $carritosIds = $execution->turno->carritos_ids ?? [];
        if (empty($carritosIds)) {
            return [];
        }

        // Get carritos that are NOT in use in active executions
        $busyCarritos = LimpiezaEjecucion::where('estado', EstadoLimpieza::EnProgreso)
            ->whereNotNull('carrito_id')
            ->pluck('carrito_id')
            ->toArray();

        /** @var array<int, string> $result */
        $result = Ubicacion::whereIn('id', $carritosIds)
            ->whereNotIn('id', $busyCarritos)
            ->pluck('nombre', 'id')
            ->toArray();

        return $result;
    }

    /** @return array<int, array{nombre: string, cantidad: float, detalles: array<int, string>}> */
    public function getAbastecimientoSugerido(): array
    {
        $colaborador = auth()->user()?->persona?->colaborador;
        if (! $colaborador) {
            return [];
        }

        $executions = LimpiezaEjecucion::whereDate('fecha', now()->toDateString())
            ->where('colaborador_id', $colaborador->id)
            ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
            ->get();

        $sugerencias = [];

        foreach ($executions as $e) {
            if ($e->limpiable_type === Habitacion::class) {
                /** @var Habitacion|null $habitacion */
                $habitacion = $e->limpiable;
                if ($habitacion) {
                    $roomStocks = Stock::with(['variante.producto'])
                        ->where('stockable_type', Habitacion::class)
                        ->where('stockable_id', $habitacion->id)
                        ->get();

                    foreach ($roomStocks as $rs) {
                        $ideal = (float) $rs->cantidad_ideal;
                        $actual = (float) $rs->cantidad_actual;
                        if ($actual < $ideal && $rs->variante) {
                            $faltante = $ideal - $actual;
                            $varianteId = $rs->variante->id;
                            $nombre = ($rs->variante->producto->nombre ?? '').($rs->variante->nombre_variante ? " ({$rs->variante->nombre_variante})" : '');

                            if (! isset($sugerencias[$varianteId])) {
                                $sugerencias[$varianteId] = [
                                    'nombre' => $nombre,
                                    'cantidad' => 0.0,
                                    'detalles' => [],
                                ];
                            }
                            $sugerencias[$varianteId]['cantidad'] += $faltante;
                            $sugerencias[$varianteId]['detalles'][] = "Hab. {$habitacion->numero}: {$faltante}";
                        }
                    }
                }
            }
        }

        return $sugerencias;
    }

    public function openCompleteModal(int $executionId): void
    {
        $execution = LimpiezaEjecucion::find($executionId);
        if (! $execution) {
            return;
        }

        $this->completingExecutionId = $executionId;
        $this->observaciones = $execution->observaciones ?? '';

        // Define default checklist tasks if empty
        $existingChecklist = $execution->detalles_checklist;
        if (empty($existingChecklist)) {
            $this->checklist = [
                ['task' => 'Tender camas y cambiar sábanas', 'completed' => false],
                ['task' => 'Sacudir polvo de superficies y mobiliario', 'completed' => false],
                ['task' => 'Limpiar y desinfectar el cuarto de baño', 'completed' => false],
                ['task' => 'Barrer y trapear los pisos', 'completed' => false],
                ['task' => 'Reponer toallas limpias', 'completed' => false],
                ['task' => 'Reponer amenidades (jabón, shampoo, café)', 'completed' => false],
                ['task' => 'Vaciar papeleras y colocar bolsas nuevas', 'completed' => false],
            ];
        } else {
            $this->checklist = [];
            foreach ($existingChecklist as $task => $status) {
                $this->checklist[] = [
                    'task' => $task,
                    'completed' => (bool) $status,
                ];
            }
        }

        // Load stock from cart for consumption inputs
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

    public function completeExecution(TerminarLimpieza $terminarLimpieza): void
    {
        if (! $this->completingExecutionId) {
            return;
        }

        $execution = LimpiezaEjecucion::find($this->completingExecutionId);
        if (! $execution) {
            $this->closeCompleteModal();

            return;
        }

        // Convert array checklist back to key-value
        $formattedChecklist = [];
        $hasDiscrepancy = false;
        foreach ($this->checklist as $item) {
            $formattedChecklist[$item['task']] = $item['completed'];
            if (! $item['completed']) {
                $hasDiscrepancy = true;
            }
        }

        // Get non-zero consumptions
        $formattedConsumos = [];
        foreach ($this->consumos as $varianteId => $info) {
            if ((float) $info['cantidad'] > 0) {
                $formattedConsumos[$varianteId] = (float) $info['cantidad'];
            }
        }

        // Complete cleaning using Use Case
        $terminarLimpieza->execute($execution, $formattedChecklist, $this->observaciones, $formattedConsumos);
        $fresh = $execution->fresh();
        $estado = $fresh?->estado;

        /** @var mixed $limpiable */
        $limpiable = $execution->limpiable;
        $nombreLimpiable = is_object($limpiable) ? ($limpiable->nombre ?? 'Área sin nombre') : 'Área sin nombre';

        Notification::make()
            ->title($estado === EstadoLimpieza::Completada ? 'Limpieza Completada' : 'Completada con Discrepancias')
            ->body("Se guardó el registro de limpieza para {$nombreLimpiable}.")
            ->success()
            ->send();

        $this->closeCompleteModal();
    }
}
