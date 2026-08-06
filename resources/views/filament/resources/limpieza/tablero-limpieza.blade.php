@use('App\Enums\Limpieza\EstadoLimpieza')
@use('App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource', 'EjecucionResource')
@use('App\Filament\Resources\Limpieza\SolicitudLimpiezaResource\SolicitudLimpiezaResource', 'SolicitudResource')

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Selector de Ubicación --}}
        <div class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Filtrar por Ubicación</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Filtre las limpiezas por zona o ubicación física.</p>
                </div>
                <div class="w-full md:flex-1 md:max-w-3xl">
                    {{ $this->form }}
                </div>
            </div>
        </div>

        {{-- Columnas del Tablero Kanban --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Columna: Pendientes --}}
            <div class="flex flex-col bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800/80 min-h-[500px]">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <h4 class="font-bold text-gray-800 dark:text-gray-100">Disponibles / Pendientes</h4>
                    </div>
                    <span class="px-2.5 py-0.5 text-xs font-semibold bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 rounded-full">
                        {{ $this->pendientes->count() + $this->solicitudes->count() }}
                    </span>
                </div>

                <div class="space-y-4 flex-1 overflow-y-auto">
                    @forelse($this->pendientesPaged as $e)
                        <x-limpieza.execution-card
                            accentColor="amber"
                            :titulo="$e->limpiable->nombre ?? 'Ubicación'"
                            :tipo="$e->limpiable_type"
                            :url="EjecucionResource::getUrl('view', ['record' => $e->id])"
                        >
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                <span>Est. Hora: {{ $e->horario->hora_estimada ?? '—' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-400" />
                                @php $nombreColab = $this->obtenerNombreColaborador($e); @endphp
                                @if ($nombreColab !== 'Sin asignar')
                                    <span>Asignado: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $nombreColab }}</span></span>
                                @else
                                    <span class="text-red-500 dark:text-red-400 font-semibold">Sin asignar / Libre</span>
                                @endif
                            </div>

                            <x-slot:footer>
                                <button wire:click="claimAndStart({{ $e->id }})" type="button"
                                    class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-700 dark:hover:bg-primary-600 rounded-lg shadow-sm transition-colors">
                                    <x-heroicon-o-play class="w-3.5 h-3.5" />
                                    Iniciar Limpieza
                                </button>
                            </x-slot:footer>
                        </x-limpieza.execution-card>
                    @empty
                        @if($this->solicitudes->isEmpty())
                            <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs">
                                No hay limpiezas pendientes en este turno.
                            </div>
                        @endif
                    @endforelse

                    {{-- Solicitudes de Limpieza (mismo diseño de card que ejecuciones) --}}
                    @foreach($this->solicitudes as $solicitud)
                        <x-limpieza.execution-card
                            accentColor="rose"
                            :titulo="$solicitud->limpiable?->nombre ?? 'Sin nombre'"
                            :tipo="$solicitud->limpiable_type"
                            :url="SolicitudResource::getUrl('edit', ['record' => $solicitud->id])"
                        >
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">SOLICITUD</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold
                                    {{ $solicitud->prioridad === 'alta' ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300' : ($solicitud->prioridad === 'baja' ? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-300') }}">
                                    {{ ucfirst($solicitud->prioridad) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                <span>{{ $solicitud->created_at?->diffForHumans() }} · {{ $solicitud->creador?->name ?? 'Sistema' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-400" />
                                @if($solicitud->personal_id)
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $solicitud->personal?->name ?? 'Asignado' }}</span>
                                @else
                                    <span class="text-red-500 dark:text-red-400 font-semibold">Sin asignar</span>
                                @endif
                            </div>

                            @if($solicitud->notas)
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 italic line-clamp-2 mt-1">
                                    "{{ $solicitud->notas }}"
                                </div>
                            @endif

                            <x-slot:footer>
                                @if($solicitud->personal_id)
                                    <div class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg">
                                        <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                        Asignada · {{ $solicitud->estado->getLabel() }}
                                    </div>
                                @else
                                    <button wire:click="asignarSolicitud({{ $solicitud->id }})" type="button"
                                        class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 dark:bg-rose-700 dark:hover:bg-rose-600 rounded-lg shadow-sm transition-colors">
                                        <x-heroicon-o-hand-raised class="w-3.5 h-3.5" />
                                        Asignar / Gestionar
                                    </button>
                                @endif
                            </x-slot:footer>
                        </x-limpieza.execution-card>
                    @endforeach

                    @if ($this->pendientes->count() > $this->pendientesLimit)
                        <div class="pt-2 text-center">
                            <button wire:click="loadMorePendientes" type="button"
                                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950 dark:text-primary-300 dark:hover:bg-primary-900 border border-primary-200 dark:border-primary-800 rounded-lg shadow-sm transition-colors">
                                <x-heroicon-o-chevron-down class="w-3.5 h-3.5" />
                                Cargar más (Mostrando {{ $this->pendientesLimit }} de {{ $this->pendientes->count() }})
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Columna: En Progreso --}}
            <div class="flex flex-col bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800/80 min-h-[500px]">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <h4 class="font-bold text-gray-800 dark:text-gray-100">En Progreso</h4>
                    </div>
                    <span class="px-2.5 py-0.5 text-xs font-semibold bg-blue-100 dark:bg-blue-950 text-blue-800 dark:bg-blue-950 dark:text-blue-300 rounded-full">
                        {{ $this->enProgreso->count() }}
                    </span>
                </div>

                <div class="space-y-4 flex-1 overflow-y-auto">
                    @forelse($this->enProgresoPaged as $e)
                        @php $isMine = $this->currentColaboradorId && $e->colaborador_id === $this->currentColaboradorId; @endphp
                        <x-limpieza.execution-card
                            accentColor="blue"
                            :titulo="$e->limpiable->nombre ?? 'Ubicación'"
                            :tipo="$e->limpiable_type"
                            :url="EjecucionResource::getUrl('view', ['record' => $e->id])"
                        >
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                <span>Inició: {{ $e->hora_inicio ? substr($e->hora_inicio, 0, 5) : '—' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-user class="w-4 h-4 text-gray-400" />
                                <span>Encargado:
                                    @if ($isMine)
                                        <span class="px-1.5 py-0.5 bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 font-bold rounded-md">Tú</span>
                                    @else
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $this->obtenerNombreColaborador($e) }}</span>
                                    @endif
                                </span>
                            </div>

                            <x-slot:footer>
                                @if ($isMine)
                                    <a href="{{ EjecucionResource::getUrl('edit', ['record' => $e->id]) }}"
                                        class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600 rounded-lg shadow-sm transition-colors">
                                        <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                        Completar Limpieza
                                    </a>
                                @else
                                    <div class="w-full text-center py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg text-xs text-gray-400 font-medium">
                                        Limpieza por {{ $this->obtenerNombreColaborador($e) }}
                                    </div>
                                @endif
                            </x-slot:footer>
                        </x-limpieza.execution-card>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs">
                            No hay habitaciones en limpieza en este momento.
                        </div>
                    @endforelse

                    @if ($this->enProgreso->count() > $this->enProgresoLimit)
                        <div class="pt-2 text-center">
                            <button wire:click="loadMoreEnProgreso" type="button"
                                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950 dark:text-primary-300 dark:hover:bg-primary-900 border border-primary-200 dark:border-primary-800 rounded-lg shadow-sm transition-colors">
                                <x-heroicon-o-chevron-down class="w-3.5 h-3.5" />
                                Cargar más (Mostrando {{ $this->enProgresoLimit }} de {{ $this->enProgreso->count() }})
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Columna: Completadas --}}
            <div class="flex flex-col bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800/80 min-h-[500px]">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <h4 class="font-bold text-gray-800 dark:text-gray-100">Completadas</h4>
                    </div>
                    <span class="px-2.5 py-0.5 text-xs font-semibold bg-green-100 dark:bg-green-950 text-green-800 dark:text-green-300 rounded-full">
                        {{ $this->completadas->count() }}
                    </span>
                </div>

                <div class="space-y-4 flex-1 overflow-y-auto">
                    @forelse($this->completadasPaged as $e)
                        @php $hasDiscrepancy = $e->estado === EstadoLimpieza::CompletadaConDiscrepancia; @endphp
                        <x-limpieza.execution-card
                            :accentColor="$hasDiscrepancy ? 'red' : 'green'"
                            :titulo="$e->limpiable->nombre ?? 'Ubicación'"
                            :tipo="$e->limpiable_type"
                            :url="EjecucionResource::getUrl('view', ['record' => $e->id])"
                        >
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-check class="w-4 h-4 text-green-500" />
                                <span>Completó: {{ $e->hora_fin ? substr($e->hora_fin, 0, 5) : '—' }}
                                    ({{ $this->obtenerNombreColaborador($e) }})</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                @if ($hasDiscrepancy)
                                    <span class="px-2 py-0.5 bg-red-100 dark:bg-red-950 text-red-800 dark:text-red-300 font-bold rounded text-[10px]">Con Novedades</span>
                                @else
                                    <span class="px-2 py-0.5 bg-green-100 dark:bg-green-950 text-green-800 dark:text-green-300 font-bold rounded text-[10px]">Sin Novedades</span>
                                @endif
                            </div>
                            @if ($e->observaciones)
                                <div class="text-[11px] text-gray-400 bg-gray-50 dark:bg-gray-900/60 p-2 rounded-lg border border-gray-100 dark:border-gray-800 mt-2">
                                    {{ $e->observaciones }}
                                </div>
                            @endif

                            <x-slot:footer>
                                <a href="{{ EjecucionResource::getUrl('view', ['record' => $e->id]) }}"
                                   class="mt-3 w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-800 border border-gray-250 dark:border-gray-800 rounded-lg shadow-sm transition-colors">
                                    <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                    Ver Detalles de la Limpieza
                                </a>
                            </x-slot:footer>
                        </x-limpieza.execution-card>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs">
                            Aún no hay habitaciones completadas.
                        </div>
                    @endforelse

                    @if ($this->completadas->count() > $this->completadasLimit)
                        <div class="pt-2 text-center">
                            <button wire:click="loadMoreCompletadas" type="button"
                                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950 dark:text-primary-300 dark:hover:bg-primary-900 border border-primary-200 dark:border-primary-800 rounded-lg shadow-sm transition-colors">
                                <x-heroicon-o-chevron-down class="w-3.5 h-3.5" />
                                Cargar más (Mostrando {{ $this->completadasLimit }} de {{ $this->completadas->count() }})
                            </button>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Modal para Completar Registro de Limpieza --}}
    @if ($completingExecutionId)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 w-full max-w-lg rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-gray-950 dark:text-white">Formulario de Checklist</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Complete las tareas antes de finalizar el reporte.</p>
                    </div>
                    <button wire:click="closeCompleteModal" type="button"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 p-1.5 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-lg transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form wire:submit.prevent="completeExecution" class="p-6 space-y-5">
                    <div class="space-y-3">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Checklist de Tareas:</label>
                        <div class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                            @foreach ($checklist as $index => $item)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200/60 dark:border-gray-800/80 hover:border-primary-300 dark:hover:border-primary-900 transition-colors">
                                    <label for="task-{{ $index }}"
                                        class="text-xs font-medium text-gray-800 dark:text-gray-200 cursor-pointer select-none pr-2">
                                        {{ $item['task'] }}
                                    </label>
                                    <input type="checkbox" id="task-{{ $index }}"
                                        wire:model="checklist.{{ $index }}.completed"
                                        class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900" />
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if (!empty($consumos))
                        <div class="space-y-3">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Registrar Consumos de Carrito:</label>
                            <div class="space-y-2 max-h-[200px] overflow-y-auto pr-1">
                                @foreach ($consumos as $varianteId => $info)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200/60 dark:border-gray-800/80">
                                        <div class="text-xs font-medium text-gray-800 dark:text-gray-200">
                                            {{ $info['nombre'] }}
                                            <span class="block text-[10px] text-gray-400">Disponible en carrito: {{ $info['max'] }}</span>
                                        </div>
                                        <div class="w-24">
                                            <input type="number" step="any" min="0" max="{{ $info['max'] }}"
                                                wire:model="consumos.{{ $varianteId }}.cantidad"
                                                class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-primary-500 focus:border-primary-500" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="space-y-1.5">
                        <label for="observaciones" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Novedades u Observaciones:</label>
                        <textarea id="observaciones" wire:model.defer="observaciones" rows="3"
                            placeholder="Ingrese novedades o discrepancias (opcional)..."
                            class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-primary-500 focus:border-primary-500 shadow-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-200 dark:border-gray-800">
                        <button wire:click="closeCompleteModal" type="button"
                            class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-700 dark:hover:bg-primary-600 rounded-lg shadow-md shadow-primary-500/10 transition-colors">
                            Guardar Registro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal para Iniciar Limpieza --}}
    <x-filament::modal id="iniciar-limpieza-modal" width="xl">
        <x-slot name="heading">Iniciar Limpieza</x-slot>
        <x-slot name="description">Asigne el colaborador y seleccione el carrito para iniciar.</x-slot>

        <form wire:submit.prevent="confirmStart" class="space-y-4">
            {{ $this->startForm }}

            <div class="flex flex-col-reverse gap-2 pt-3 border-t border-gray-200 dark:border-gray-800 sm:flex-row sm:justify-end">
                <x-filament::button color="gray" wire:click="closeStartModal" type="button" class="w-full sm:w-auto">
                    Cancelar
                </x-filament::button>
                <x-filament::button type="submit" color="primary" icon="heroicon-o-play" class="w-full sm:w-auto">
                    Iniciar Limpieza
                </x-filament::button>
            </div>
        </form>
    </x-filament::modal>
</x-filament-panels::page>
