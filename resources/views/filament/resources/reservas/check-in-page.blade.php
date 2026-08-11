<x-filament-panels::page>

    @if ($this->reserva === null)
        {{-- ══ Pantalla de selección de reserva ══ --}}

        {{-- Métricas --}}
        @php $m = $this->getMetricasCheckIn(); @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="fi-wi-stats-overview-stat relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6 text-info-500" />
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Confirmadas</span>
                </div>
                <div class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ $m['confirmadas_total'] }}
                </div>
            </div>

            <div class="fi-wi-stats-overview-stat relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6 text-warning-500" />
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Entradas Hoy</span>
                </div>
                <div class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ $m['pendientes_hoy'] }}
                </div>
            </div>

            <div class="fi-wi-stats-overview-stat relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <x-filament::icon icon="heroicon-o-key" class="h-6 w-6 text-success-500" />
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Check-Ins Hoy</span>
                </div>
                <div class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ $m['realizadas_hoy'] }}
                </div>
            </div>
        </div>

        <x-filament::section>
            <x-slot name="heading">Reservaciones Pendientes de Check-In</x-slot>
            <x-slot name="headerStart">
                <x-filament::icon icon="heroicon-o-table-cells" class="w-5 h-5 text-primary-500" />
            </x-slot>
            {{ $this->table }}
        </x-filament::section>

    @else
        {{-- ══ Wizard de Check-In ══ --}}

        <x-filament::section>
            <x-slot name="heading">
                Check-In — {{ $this->reserva->codigo_reserva }}
            </x-slot>
            <x-slot name="description">
                Titular: {{ $this->reserva->nombre_cliente }}
                &nbsp;·&nbsp;
                {{ $this->reserva->fecha_check_in?->format('d/m/Y') }} → {{ $this->reserva->fecha_check_out?->format('d/m/Y') }}
            </x-slot>
            <x-slot name="headerStart">
                <x-filament::icon icon="heroicon-o-key" class="w-5 h-5 text-success-500" />
            </x-slot>

            <form wire:submit.prevent="submit">
                {{ $this->form }}
            </form>
        </x-filament::section>

    @endif

</x-filament-panels::page>
