@use(App\Support\MonedaHelper)
@php
    $tendenciaColor = abs($deltaOcupacionPuntos) < 0.05
        ? 'text-gray-500 dark:text-gray-400'
        : ($deltaOcupacionPuntos > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400');
    $tendenciaFlecha = abs($deltaOcupacionPuntos) < 0.05 ? '→' : ($deltaOcupacionPuntos > 0 ? '▲' : '▼');
    $limpiezaTono = $limpiezasPendientes > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400';
@endphp

<x-filament-widgets::widget class="fi-wi-radial-ocupacion">
    <x-filament::section
        description="La ocupación del período, sus ingresos y la operación del día en una sola mirada."
        heading="Panorama del negocio"
    >
        <div class="grid gap-6 md:grid-cols-3">
            {{-- Anillo de ocupación --}}
            <div class="flex items-center gap-5">
                <div
                    class="relative h-44 w-44 flex-none"
                    style="background: conic-gradient(#6b003e {{ $ocupacion }}%, rgba(100, 116, 139, 0.25) {{ $ocupacion }}%); border-radius: 9999px;"
                    role="img"
                    aria-label="Ocupación del período: {{ number_format($ocupacion, 1) }} por ciento"
                >
                    <div class="absolute inset-[10px] flex flex-col items-center justify-center rounded-full bg-white shadow-inner dark:bg-gray-900">
                        <span class="text-3xl font-black tracking-tight text-gray-950 dark:text-white">
                            {{ number_format($ocupacion, 1) }}%
                        </span>
                        <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                            Ocupación
                        </span>
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">
                        {{ $habitacionesOcupadasHoy }}
                        <span class="font-normal text-gray-500 dark:text-gray-400">de {{ $habitacionesTotales }} habitaciones hoy</span>
                    </p>
                    <p class="flex items-center gap-1.5 text-sm font-semibold {{ $tendenciaColor }}">
                        <span aria-hidden="true">{{ $tendenciaFlecha }}</span>
                        {{ number_format(abs($deltaOcupacionPuntos), 1) }} pp vs período anterior
                    </p>
                </div>
            </div>

            {{-- Ingresos del período --}}
            <div class="flex flex-col justify-center rounded-xl bg-gray-50 p-5 dark:bg-white/5">
                <span class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                    Ingresos del período
                </span>
                <span class="mt-1 text-3xl font-black tracking-tight text-gray-950 dark:text-white">
                    {{ MonedaHelper::formatear($ingresos) }}
                </span>
                <span class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $reservas }} reservas registradas
                </span>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">ADR</span>
                        <p class="text-lg font-bold text-gray-950 dark:text-white">{{ MonedaHelper::formatear($adr) }}</p>
                    </div>
                    <div>
                        <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">RevPAR</span>
                        <p class="text-lg font-bold text-gray-950 dark:text-white">{{ MonedaHelper::formatear($revpar) }}</p>
                    </div>
                </div>
            </div>

            {{-- Operación de hoy --}}
            <div class="flex flex-col justify-center gap-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Check-in hoy</span>
                        <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ $checkInHoy }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <span class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Check-out hoy</span>
                        <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ $checkOutHoy }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 dark:border-white/10">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Limpiezas pendientes
                    </span>
                    <span class="text-sm font-bold {{ $limpiezaTono }}">
                        {{ $limpiezasPendientes }} / {{ $limpiezasProgramadas }}
                    </span>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>