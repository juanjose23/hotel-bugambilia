@use(App\Support\MonedaHelper)
@php
    $metricas = $this->getMetricasCheckOut();
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-xs border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Estancias Activas</span>
            <div class="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                <x-filament::icon icon="heroicon-o-home" class="h-5 w-5" />
            </div>
        </div>
        <div class="mt-3 text-3xl font-black tracking-tight text-gray-950 dark:text-white">
            {{ $metricas['checked_in_total'] }}
        </div>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Huéspedes en casa (In-House)</p>
    </div>

    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-xs border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Salidas de Hoy</span>
            <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
            </div>
        </div>
        <div class="mt-3 text-3xl font-black tracking-tight text-amber-600 dark:text-amber-400">
            {{ $metricas['salidas_hoy'] }}
        </div>
        <p class="mt-1 text-xs text-amber-600/80 dark:text-amber-400/80">Salidas programadas para hoy</p>
    </div>

    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-xs border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Check-Outs Hoy</span>
            <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                <x-filament::icon icon="heroicon-o-arrow-right-on-rectangle" class="h-5 w-5" />
            </div>
        </div>
        <div class="mt-3 text-3xl font-black tracking-tight text-emerald-600 dark:text-emerald-400">
            {{ $metricas['finalizadas_hoy'] }}
        </div>
        <p class="mt-1 text-xs text-emerald-600/80 dark:text-emerald-400/80">Habitaciones liberadas hoy</p>
    </div>

    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-5 shadow-xs border border-gray-200 dark:border-gray-700 transition hover:shadow-md">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Saldos por Cobrar</span>
            <div class="p-2.5 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5" />
            </div>
        </div>
        <div class="mt-3 text-3xl font-black tracking-tight {{ ((float) $metricas['saldo_pendiente_total']) > 0 ? 'text-purple-600 dark:text-purple-400' : 'text-emerald-600 dark:text-emerald-400' }}">
            {{ MonedaHelper::formatear((float) $metricas['saldo_pendiente_total']) }}
        </div>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Total pendiente en folios activos</p>
    </div>
</div>
