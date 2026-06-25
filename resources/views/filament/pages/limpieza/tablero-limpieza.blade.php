@php
    use App\Enums\HabitacionesEspacios\EstadoLimpieza;
    use App\Models\Habitaciones\Habitacion;
    use App\Models\Espacios\Espacio;
    use App\Models\Catalogos\Ubicacion;
@endphp
<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Selector de Turno y Ubicación -->
        <!-- Selector de Ubicación -->
        <div
            class="bg-white dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Filtrar por Ubicación</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Filtre las limpiezas por zona o ubicación física.
                    </p>
                </div>
                <div class="w-full md:w-80">
                    {{ $this->form }}
                </div>
            </div>
        </div>



        @php
            $currentColaborador = auth()->user()->persona?->colaborador;
            $currentColaboradorId = $currentColaborador?->id;

            $pendientes = $this->executions->filter(fn($e) => $e->estado === EstadoLimpieza::Pendiente);
            $enProgreso = $this->executions->filter(fn($e) => $e->estado === EstadoLimpieza::EnProgreso);
            $completadas = $this->executions->filter(
                fn($e) => in_array($e->estado, [
                    EstadoLimpieza::Completada,
                    EstadoLimpieza::CompletadaConDiscrepancia,
                ]),
            );
        @endphp

        <!-- Columnas del Tablero Kanban -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Columna: Pendientes -->
            <div
                class="flex flex-col bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800/80 min-h-[500px]">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <h4 class="font-bold text-gray-800 dark:text-gray-100">Disponibles / Pendientes</h4>
                    </div>
                    <span
                        class="px-2.5 py-0.5 text-xs font-semibold bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 rounded-full">
                        {{ count($pendientes) }}
                    </span>
                </div>

                <div class="space-y-4 flex-1 overflow-y-auto">
                    @forelse($pendientes as $e)
                        @php
                            $tipoLabel = match ($e->limpiable_type) {
                                \App\Models\Habitaciones\Habitacion::class => 'Habitación',
                                \App\Models\Espacios\Espacio::class => 'Espacio Común',
                                \App\Models\Catalogos\Ubicacion::class => 'Ubicación Física',
                                default => 'Otro',
                            };
                            $tipoColor = match ($e->limpiable_type) {
                                \App\Models\Habitaciones\Habitacion::class
                                    => 'bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                \App\Models\Espacios\Espacio::class
                                    => 'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                \App\Models\Catalogos\Ubicacion::class
                                    => 'bg-teal-50 dark:bg-teal-950 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800',
                                default => 'bg-gray-50 text-gray-700',
                            };
                        @endphp
                        <div
                            class="bg-white dark:bg-gray-950 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h5 class="font-bold text-gray-950 dark:text-white text-base">
                                    {{ $e->limpiable->nombre ?? 'Ubicación' }}
                                </h5>
                                <span class="text-[10px] px-2 py-0.5 font-medium rounded-md border {{ $tipoColor }}">
                                    {{ $tipoLabel }}
                                </span>
                            </div>

                            <div class="space-y-1.5 text-xs text-gray-500 dark:text-gray-400 mb-4">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                    <span>Est. Hora: {{ $e->horario->hora_estimada ?? '—' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-o-user class="w-4 h-4 text-gray-400" />
                                    @if ($e->colaborador_id)
                                        @php
                                            $colabName = $e->colaborador && $e->colaborador->persona
                                                ? \App\UseCases\Shared\Queries\ObtenerNombrePersona::desde($e->colaborador->persona)
                                                : "Colaborador #{$e->colaborador_id}";
                                        @endphp
                                        <span>Asignado: <span
                                                class="font-medium text-gray-700 dark:text-gray-300">{{ $colabName }}</span></span>
                                    @else
                                        <span class="text-red-500 dark:text-red-400 font-semibold">Sin asignar /
                                            Libre</span>
                                    @endif
                                </div>
                            </div>

                            <button wire:click="claimAndStart({{ $e->id }})" type="button"
                                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-700 dark:hover:bg-primary-600 rounded-lg shadow-sm transition-colors">
                                <x-heroicon-o-play class="w-3.5 h-3.5" />
                                Iniciar Limpieza
                            </button>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs">
                            No hay limpiezas pendientes en este turno.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Columna: En Progreso -->
            <div
                class="flex flex-col bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800/80 min-h-[500px]">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <h4 class="font-bold text-gray-800 dark:text-gray-100">En Progreso</h4>
                    </div>
                    <span
                        class="px-2.5 py-0.5 text-xs font-semibold bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 rounded-full">
                        {{ count($enProgreso) }}
                    </span>
                </div>

                <div class="space-y-4 flex-1 overflow-y-auto">
                    @forelse($enProgreso as $e)
                        @php
                            $tipoLabel = match ($e->limpiable_type) {
                                Habitacion::class => 'Habitación',
                                Espacio::class => 'Espacio Común',
                                Ubicacion::class => 'Ubicación Física',
                                default => 'Otro',
                            };
                            $tipoColor = match ($e->limpiable_type) {
                                Habitacion::class
                                    => 'bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                Espacio::class
                                    => 'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                Ubicacion::class
                                    => 'bg-teal-50 dark:bg-teal-950 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800',
                                default => 'bg-gray-50 text-gray-700',
                            };
                            $isMine = $currentColaboradorId && $e->colaborador_id === $currentColaboradorId;
                            $colabName = $e->colaborador && $e->colaborador->persona
                                ? \App\UseCases\Shared\Queries\ObtenerNombrePersona::desde($e->colaborador->persona)
                                : "Colaborador #{$e->colaborador_id}";
                        @endphp
                        <div
                            class="bg-white dark:bg-gray-950 p-4 rounded-xl border border-blue-200 dark:border-blue-900/50 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-1.5 h-full bg-blue-500"></div>

                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h5 class="font-bold text-gray-950 dark:text-white text-base">
                                    {{ $e->limpiable->nombre ?? 'Ubicación' }}
                                </h5>
                                <span
                                    class="text-[10px] px-2 py-0.5 font-medium rounded-md border {{ $tipoColor }}">
                                    {{ $tipoLabel }}
                                </span>
                            </div>

                            <div class="space-y-1.5 text-xs text-gray-500 dark:text-gray-400 mb-4">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                                    <span>Inició:
                                        {{ $e->hora_inicio ? substr($e->hora_inicio, 0, 5) : '—' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-o-user class="w-4 h-4 text-gray-400" />
                                    <span>Encargado:
                                        @if ($isMine)
                                            <span
                                                class="px-1.5 py-0.5 bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 font-bold rounded-md">Tú</span>
                                        @else
                                            <span
                                                class="font-medium text-gray-700 dark:text-gray-300">{{ $colabName }}</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            @if ($isMine)
                                <button wire:click="openCompleteModal({{ $e->id }})" type="button"
                                    class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-500 dark:bg-blue-700 dark:hover:bg-blue-600 rounded-lg shadow-sm transition-colors">
                                    <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                    Completar Limpieza
                                </button>
                            @else
                                <div
                                    class="w-full text-center py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg text-xs text-gray-400 font-medium">
                                    Limpieza por {{ $colabName }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs">
                            No hay habitaciones en limpieza en este momento.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Columna: Completadas -->
            <div
                class="flex flex-col bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800/80 min-h-[500px]">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <h4 class="font-bold text-gray-800 dark:text-gray-100">Completadas</h4>
                    </div>
                    <span
                        class="px-2.5 py-0.5 text-xs font-semibold bg-green-100 dark:bg-green-950 text-green-800 dark:text-green-300 rounded-full">
                        {{ count($completadas) }}
                    </span>
                </div>

                <div class="space-y-4 flex-1 overflow-y-auto">
                    @forelse($completadas as $e)
                        @php
                            $tipoLabel = match ($e->limpiable_type) {
                                Habitacion::class => 'Habitación',
                                Espacio::class => 'Espacio Común',
                                Ubicacion::class => 'Ubicación Física',
                                default => 'Otro',
                            };
                            $tipoColor = match ($e->limpiable_type) {
                                Habitacion::class
                                    => 'bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                Espacio::class
                                    => 'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                Ubicacion::class
                                    => 'bg-teal-50 dark:bg-teal-950 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800',
                                default => 'bg-gray-50 text-gray-700',
                            };
                            $colabName = $e->colaborador && $e->colaborador->persona
                                ? \App\UseCases\Shared\Queries\ObtenerNombrePersona::desde($e->colaborador->persona)
                                : "Colaborador #{$e->colaborador_id}";
                            $hasDiscrepancy = $e->estado === EstadoLimpieza::CompletadaConDiscrepancia;
                        @endphp
                        <div
                            class="bg-white dark:bg-gray-950 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden">
                            <div
                                class="absolute top-0 right-0 w-1.5 h-full {{ $hasDiscrepancy ? 'bg-red-500' : 'bg-green-500' }}">
                            </div>

                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h5 class="font-bold text-gray-950 dark:text-white text-base">
                                    {{ $e->limpiable->nombre ?? 'Ubicación' }}
                                </h5>
                                <span
                                    class="text-[10px] px-2 py-0.5 font-medium rounded-md border {{ $tipoColor }}">
                                    {{ $tipoLabel }}
                                </span>
                            </div>

                            <div class="space-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-o-check class="w-4 h-4 text-green-500" />
                                    <span>Completó: {{ $e->hora_fin ? substr($e->hora_fin, 0, 5) : '—' }}
                                        ({{ $colabName }})</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    @if ($hasDiscrepancy)
                                        <span
                                            class="px-2 py-0.5 bg-red-100 dark:bg-red-950 text-red-800 dark:text-red-300 font-bold rounded text-[10px]">Con
                                            Novedades</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 bg-green-100 dark:bg-green-950 text-green-800 dark:text-green-300 font-bold rounded text-[10px]">Sin
                                            Novedades</span>
                                    @endif
                                </div>
                                @if ($e->observaciones)
                                    <div
                                        class="text-[11px] text-gray-400 bg-gray-50 dark:bg-gray-900/60 p-2 rounded-lg border border-gray-100 dark:border-gray-800 mt-2">
                                        {{ $e->observaciones }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500 text-xs">
                            Aún no hay habitaciones completadas.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- Modal para Completar Registro de Limpieza -->
    @if ($completingExecutionId)
        <div
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div
                class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 w-full max-w-lg rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in duration-200">

                <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-gray-950 dark:text-white">Formulario de Checklist</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Complete las tareas antes de finalizar el
                            reporte.</p>
                    </div>
                    <button wire:click="closeCompleteModal" type="button"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 p-1.5 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-lg transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form wire:submit.prevent="completeExecution" class="p-6 space-y-5">
                    <!-- Lista de Checklist -->
                    <div class="space-y-3">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Checklist de
                            Tareas:</label>
                        <div class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                            @foreach ($checklist as $index => $item)
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200/60 dark:border-gray-800/80 hover:border-primary-300 dark:hover:border-primary-900 transition-colors">
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

                    <!-- Insumos Consumidos (Stock) -->
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

                    <!-- Observaciones -->
                    <div class="space-y-1.5">
                        <label for="observaciones"
                            class="text-sm font-semibold text-gray-700 dark:text-gray-300">Novedades u
                            Observaciones:</label>
                        <textarea id="observaciones" wire:model.defer="observaciones" rows="3"
                            placeholder="Ingrese novedades o discrepancias (opcional)..."
                            class="w-full text-xs rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-primary-500 focus:border-primary-500 shadow-sm"></textarea>
                    </div>

                    <!-- Botones de Acción -->
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

    <!-- Modal para Iniciar Limpieza (Seleccionar Carrito) -->
    @if ($startingExecutionId)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 w-full max-w-md rounded-2xl shadow-xl overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-gray-950 dark:text-white">Seleccionar Carrito</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Seleccione el carrito o bodega de limpieza para iniciar.</p>
                    </div>
                    <button wire:click="closeStartModal" type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 p-1.5 hover:bg-gray-100 dark:hover:bg-gray-900 rounded-lg transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
                <form wire:submit.prevent="confirmStart" class="p-6 space-y-5">
                    <div class="space-y-1.5">
                        <label for="selectedCarritoId" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Carrito de Limpieza:</label>
                        @php
                            $carritos = $this->getAvailableCarritos();
                        @endphp
                        @if (empty($carritos))
                            <div class="p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 rounded-xl text-xs text-red-600 dark:text-red-400 font-medium">
                                No hay carritos de limpieza disponibles actualmente para este turno (todos están ocupados o sin asignar).
                            </div>
                        @else
                            <x-filament::input.wrapper>
                                <x-filament::input.select id="selectedCarritoId" wire:model="selectedCarritoId">
                                    <option value="">-- Seleccione un Carrito --</option>
                                    @foreach ($carritos as $id => $nombre)
                                        <option value="{{ $id }}">{{ $nombre }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        @endif
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-200 dark:border-gray-800">
                        <button wire:click="closeStartModal" type="button" class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm transition-colors">
                             Cancelar
                        </button>
                        <button type="submit" @if(empty($carritos)) disabled @endif class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-700 dark:hover:bg-primary-600 rounded-lg shadow-md shadow-primary-500/10 transition-colors disabled:opacity-50">
                             Iniciar Turno
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-filament-panels::page>
