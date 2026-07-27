<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Encabezado Operativo de Recepción --}}
        <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 rounded-xl">
                        <x-filament::icon icon="heroicon-o-arrow-right-on-rectangle" class="w-6 h-6" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            @if ($this->reserva !== null)
                                Completar Check-Out — {{ $this->reserva->codigo_reserva }}
                            @else
                                Registro de Check-Out
                            @endif
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @if ($this->reserva !== null)
                                Verifique la cuenta de consumos, devolución de llaves y finalización de la estancia de {{ $this->reserva->nombre_cliente }}.
                            @else
                                Seleccione una estancia activa de la tabla a continuación para registrar la salida del huésped.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if ($this->reserva === null)
            {{-- Tarjetas de Indicadores Estadísticos --}}
            @php
                $metricas = $this->getMetricasCheckOut();
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-xs border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Estancias Activas</span>
                        <x-filament::icon icon="heroicon-o-home" class="w-5 h-5 text-blue-500" />
                    </div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white mt-2">
                        {{ $metricas['checked_in_total'] }}
                    </div>
                </div>

                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-xs border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Salidas Pendientes (Hoy)</span>
                        <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-amber-500" />
                    </div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-2">
                        {{ $metricas['salidas_hoy'] }}
                    </div>
                </div>

                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-xs border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Check-Outs Completados (Hoy)</span>
                        <x-filament::icon icon="heroicon-o-arrow-right-on-rectangle" class="w-5 h-5 text-emerald-500" />
                    </div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                        {{ $metricas['finalizadas_hoy'] }}
                    </div>
                </div>
            </div>

            {{-- Tabla Principal de Estancias Activas --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-table-cells" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    Estancias Activas Pendientes de Salida
                </h3>
                {{ $this->table }}
            </div>
        @else
            {{-- Formulario Dedicado de Check-Out para la Reserva Seleccionada --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xs space-y-6">
                <form wire:submit.prevent="submit" class="space-y-6">
                    {{ $this->form }}

                    <div class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-gray-700">
                        <x-filament::button
                            type="button"
                            color="gray"
                            icon="heroicon-o-arrow-left"
                            wire:click="$set('record', null)"
                        >
                            Cancelar / Volver a la Lista
                        </x-filament::button>

                        <x-filament::button
                            type="submit"
                            color="warning"
                            icon="heroicon-o-arrow-right-on-rectangle"
                            size="lg"
                        >
                            Completar y Registrar Check-Out
                        </x-filament::button>
                    </div>
                </form>
            </div>
        @endif

    </div>
</x-filament-panels::page>
