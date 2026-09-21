@use(App\Support\MonedaHelper)
<x-filament-panels::page>

    @if ($this->reserva === null)
        {{-- ══ Dashboard & Lista de Recepción (Llegadas) ══ --}}
        @php $m = $this->getMetricasCheckIn(); @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-xs border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Confirmadas</span>
                    <div class="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <x-filament::icon icon="heroicon-o-check-badge" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 text-3xl font-black tracking-tight text-gray-950 dark:text-white">
                    {{ $m['confirmadas_total'] }}
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Reservas activas en sistema</p>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-xs border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Entradas de Hoy</span>
                    <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                        <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 text-3xl font-black tracking-tight text-amber-600 dark:text-amber-400">
                    {{ $m['pendientes_hoy'] }}
                </div>
                <p class="mt-1 text-xs text-amber-600/80 dark:text-amber-400/80">Llegadas programadas hoy</p>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-xs border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Check-Ins Hoy</span>
                    <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                        <x-filament::icon icon="heroicon-o-key" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 text-3xl font-black tracking-tight text-emerald-600 dark:text-emerald-400">
                    {{ $m['realizadas_hoy'] }}
                </div>
                <p class="mt-1 text-xs text-emerald-600/80 dark:text-emerald-400/80">Huéspedes ingresados hoy</p>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-xs border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Habitaciones Listas</span>
                    <div class="p-2.5 rounded-xl bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400">
                        <x-filament::icon icon="heroicon-o-home" class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 text-3xl font-black tracking-tight text-teal-600 dark:text-teal-400">
                    {{ $m['habitaciones_disponibles'] }}
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Limpias y disponibles</p>
            </div>
        </div>

        {{-- Tabla de Reservas --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-user-plus" class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Reservaciones Pendientes de Ingreso</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Seleccione una reservación para abrir el Centro de Comando de Check-In</p>
                    </div>
                </div>
            </div>
            {{ $this->table }}
        </div>

    @else
        {{-- ══ COMMAND CENTER SPLIT-PANE DE CHECK-IN ══ --}}
        @php
            $readiness = $this->getReadiness();
            $ci = $this->reserva->fecha_check_in;
            $co = $this->reserva->fecha_check_out;
            $noches = ($ci && $co) ? max(1, (int) $ci->diffInDays($co)) : 1;
        @endphp

        <form wire:submit.prevent="submit" class="space-y-6">

            {{-- Header Ejecutivo de Check-In --}}
            <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="p-3.5 bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                            <x-filament::icon icon="heroicon-o-key" class="w-8 h-8" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h2 class="text-2xl font-black text-gray-900 dark:text-white">
                                    Check-In: {{ $this->reserva->nombre_cliente ?? 'Huésped' }}
                                </h2>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300">
                                    {{ $this->reserva->codigo_reserva }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Asignación de habitación, registro de huéspedes, llaves y apertura de folio de consumo.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold {{ $readiness['puede_realizar_check_in'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30' }}">
                            <x-filament::icon :icon="$readiness['puede_realizar_check_in'] ? 'heroicon-o-check-circle' : 'heroicon-o-information-circle'" class="w-4 h-4" />
                            {{ $readiness['puede_realizar_check_in'] ? 'LISTO PARA CHECK-IN' : 'VERIFICACIONES PENDIENTES' }}
                        </div>

                        <x-filament::button
                            type="button"
                            color="gray"
                            icon="heroicon-o-arrow-left"
                            wire:click="volverALista"
                            size="sm"
                        >
                            Volver a Lista
                        </x-filament::button>
                    </div>
                </div>
            </div>

            {{-- Split-Pane: 2 Columnas (8 cols Principal / 4 cols Terminal Sticky) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                {{-- Columna Principal: Formularios (8 cols) --}}
                <div class="lg:col-span-8 space-y-6">
                    {{ $this->form }}
                </div>

                {{-- Columna Lateral: Terminal Sticky de Readiness y Cierre (4 cols) --}}
                <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-6">

                    {{-- Card de Semáforo de Readiness --}}
                    <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Readiness de Habitación</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-md {{ $readiness['puede_realizar_check_in'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                {{ $readiness['puede_realizar_check_in'] ? 'Listo' : 'Atención' }}
                            </span>
                        </div>

                        <div class="space-y-2 text-xs font-medium">
                            <div class="flex items-center justify-between p-2.5 rounded-xl {{ ($readiness['habitacion_limpia'] ?? false) ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300' : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300' }}">
                                <span class="flex items-center gap-2">
                                    <x-filament::icon :icon="($readiness['habitacion_limpia'] ?? false) ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'" class="w-4 h-4" />
                                    Limpieza / Housekeeping
                                </span>
                                <span class="font-bold">{{ $readiness['estado_habitacion_label'] ?? '—' }}</span>
                            </div>

                            <div class="flex items-center justify-between p-2.5 rounded-xl {{ ($readiness['sin_bloqueo_mantenimiento'] ?? false) ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300' : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300' }}">
                                <span class="flex items-center gap-2">
                                    <x-filament::icon :icon="($readiness['sin_bloqueo_mantenimiento'] ?? false) ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'" class="w-4 h-4" />
                                    Mantenimiento
                                </span>
                                <span class="font-bold">{{ ($readiness['sin_bloqueo_mantenimiento'] ?? false) ? 'Sin bloqueo' : 'En mantenimiento' }}</span>
                            </div>

                            <div class="flex items-center justify-between p-2.5 rounded-xl {{ ($readiness['reserva_confirmada'] ?? false) ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300' : 'bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-300' }}">
                                <span class="flex items-center gap-2">
                                    <x-filament::icon :icon="($readiness['reserva_confirmada'] ?? false) ? 'heroicon-o-check-circle' : 'heroicon-o-clock'" class="w-4 h-4" />
                                    Reserva Confirmada
                                </span>
                                <span class="font-bold">{{ ($readiness['reserva_confirmada'] ?? false) ? 'Válida' : 'Pendiente' }}</span>
                            </div>
                        </div>

                        {{-- Advertencias o Bloqueos --}}
                        @if (! empty($readiness['bloqueos']))
                            <div class="p-3 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-xl text-xs space-y-1">
                                <strong>Bloqueos de ingreso:</strong>
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach ($readiness['bloqueos'] as $bloqueo)
                                        <li>{{ $bloqueo }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- Card Resumen de Estancia --}}
                    <div class="p-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs space-y-3 text-xs">
                        <span class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Detalles del Ingreso</span>

                        <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-400 font-medium">Habitación:</span>
                                <strong class="text-gray-900 dark:text-white">
                                    @if ($this->reserva->habitacion)
                                        Hab. {{ $this->reserva->habitacion->numero }} — {{ $this->reserva->habitacion->nombre }}
                                    @else
                                        {{ $this->reserva->espacio?->nombre ?? 'Por asignar' }}
                                    @endif
                                </strong>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-400 font-medium">Estancia:</span>
                                <strong class="text-gray-900 dark:text-white">{{ $ci?->format('d/m/Y') }} → {{ $co?->format('d/m/Y') }} ({{ $noches }} n.)</strong>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-400 font-medium">Ocupantes:</span>
                                <strong class="text-gray-900 dark:text-white">{{ $this->reserva->adultos }} ad.{{ $this->reserva->ninos > 0 ? " + {$this->reserva->ninos} niñ." : '' }}</strong>
                            </div>

                            <div class="flex justify-between pt-1 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-gray-400 font-medium">Saldo Reserva:</span>
                                <strong class="{{ (float) $this->reserva->saldo > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ MonedaHelper::formatear((float) $this->reserva->saldo, $this->reserva->moneda) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{-- Botón Principal de Check-In --}}
                    <div class="space-y-3">
                        <x-filament::button
                            type="submit"
                            color="success"
                            icon="heroicon-o-key"
                            size="xl"
                            class="w-full py-4 text-base font-bold shadow-lg"
                        >
                            REALIZAR CHECK-IN Y ENTREGAR LLAVES
                        </x-filament::button>

                        <x-filament::button
                            type="button"
                            color="gray"
                            icon="heroicon-o-arrow-left"
                            wire:click="volverALista"
                            class="w-full"
                            size="sm"
                        >
                            Cancelar / Volver a Lista
                        </x-filament::button>
                    </div>

                </div>

            </div>

        </form>
    @endif

</x-filament-panels::page>
