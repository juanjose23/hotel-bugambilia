@php
    use App\Enums\Reportes\SeveridadAnomalia;

    $mapaColor = [
        SeveridadAnomalia::Critica->value => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-300',
        SeveridadAnomalia::Alta->value => 'bg-orange-50 text-orange-700 ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-300',
        SeveridadAnomalia::Media->value => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300',
        SeveridadAnomalia::Baja->value => 'bg-slate-50 text-slate-600 ring-slate-500/20 dark:bg-white/5 dark:text-slate-300',
    ];
@endphp

<x-filament-widgets::widget class="fi-wi-anomalias-operacion">
    <x-filament::section
        description="Alertas clasificadas por severidad con umbrales configurables en BusinessLogic."
        heading="Anomalías operativas"
    >
        @if (empty($anomalias))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                <x-filament::icon
                    icon="heroicon-o-check-circle"
                    class="h-6 w-6 text-emerald-600 dark:text-emerald-400"
                />
                <div>
                    <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Sin anomalías en el período</p>
                    <p class="text-sm text-emerald-700 dark:text-emerald-400">Todos los indicadores operativos están dentro de los umbrales.</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase tracking-widest text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="px-3 py-2">Alerta</th>
                            <th class="px-3 py-2">Actual / Umbral</th>
                            <th class="px-3 py-2">Severidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($anomalias as $anomalia)
                            @php
                                $colorSeveridad = $mapaColor[$anomalia['severidad']->value] ?? $mapaColor[SeveridadAnomalia::Baja->value];
                            @endphp
                            <tr>
                                <td class="px-3 py-3">
                                    <p class="font-medium text-gray-950 dark:text-white">{{ $anomalia['titulo'] }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $anomalia['descripcion'] }}</p>
                                </td>
                                <td class="px-3 py-3 font-semibold text-gray-700 dark:text-gray-300">
                                    {{ number_format($anomalia['cantidad'], $anomalia['cantidad'] == (int) $anomalia['cantidad'] ? 0 : 1) }}
                                    / {{ $anomalia['umbral'] }}
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $colorSeveridad }}">
                                        {{ $anomalia['severidad']->getLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if (count($bloqueadas) > 0 || count($mantenimientosVencidos) > 0)
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @if (count($bloqueadas) > 0)
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Habitaciones bloqueadas hoy</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($bloqueadas as $habitacion)
                                <li class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $habitacion['habitacion'] }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">{{ $habitacion['estado'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (count($mantenimientosVencidos) > 0)
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Mantenimientos vencidos</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($mantenimientosVencidos as $mantenimiento)
                                <li class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $mantenimiento['activo'] }}</span>
                                    <span class="ml-1 text-gray-500 dark:text-gray-400">· {{ $mantenimiento['plan'] }} · {{ $mantenimiento['fecha'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>