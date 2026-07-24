<x-filament-panels::page>
    <div class="space-y-6">

        {{-- FORMULARIO NATIVO DE FILAMENT PARA FILTROS --}}
        <div>
            {{ $this->form }}
        </div>
        {{-- CABECERA DEL CALENDARIO CON NAVEGACIÓN Y BOTONES HOY / ANTERIOR / SIGUIENTE --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 rounded-xl shadow-xs ring-1 ring-primary-100/10">
                    <x-filament::icon icon="heroicon-o-calendar" class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="text-xl font-extrabold text-gray-950 dark:text-white">
                        {{ $calendarioData['nombreMes'] ?? '' }} de {{ $calendarioData['year'] ?? '' }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Visualización mensual de reservaciones ocupadas por día de ingreso.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-filament::button
                    wire:click="previousMonth"
                    color="gray"
                    icon="heroicon-m-chevron-left"
                    size="sm"
                    class="hover:scale-[1.02] transition-transform"
                >
                    Anterior
                </x-filament::button>

                <x-filament::button
                    wire:click="goToToday"
                    color="gray"
                    size="sm"
                    class="font-semibold hover:scale-[1.02] transition-transform"
                >
                    Hoy
                </x-filament::button>

                <x-filament::button
                    wire:click="nextMonth"
                    color="gray"
                    icon="heroicon-m-chevron-right"
                    icon-position="after"
                    size="sm"
                    class="hover:scale-[1.02] transition-transform"
                >
                    Siguiente
                </x-filament::button>
            </div>
        </div>

        {{-- LEYENDA DE ESTADOS CON ESTILO BADGES --}}
        <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-gray-600 dark:text-gray-400 px-1">
            <span class="flex items-center gap-1.5">
                <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                Confirmada
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-3 w-3 rounded-full bg-sky-500"></span>
                En Estancia (Checked In / Out)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                Pendiente
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                Cancelada
            </span>
        </div>

        {{-- CUADRÍCULA DEL CALENDARIO DE 7 COLUMNAS (ESTILO CALENDARIO DE ACTIVOS) --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
            <!-- Días de la semana -->
            <div class="grid grid-cols-7 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20 text-center py-3 font-bold text-xs text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                <div>Lunes</div>
                <div>Martes</div>
                <div>Miércoles</div>
                <div>Jueves</div>
                <div>Viernes</div>
                <div>Sábado</div>
                <div>Domingo</div>
            </div>

            <!-- Celdas de los días -->
            <div class="grid grid-cols-7 grid-flow-row divide-x divide-y divide-gray-100 dark:divide-gray-700 bg-gray-50/20 dark:bg-gray-900/10">
                @php
                    $days = $calendarioData['days'] ?? [];
                    $reservasPorDia = $calendarioData['reservasPorDia'] ?? collect();
                    $currentMonth = $calendarioData['month'] ?? now()->month;
                    $currentYear = $calendarioData['year'] ?? now()->year;
                @endphp

                @foreach ($days as $day)
                    @if ($day === null)
                        <!-- Celda vacía para días de offset del mes anterior -->
                        <div class="min-h-[140px] bg-gray-50/40 dark:bg-gray-900/40 p-2"></div>
                    @else
                        @php
                            $esHoy = $day === (int) now()->day && $currentMonth === (int) now()->month && $currentYear === (int) now()->year;
                            $reservasDia = $reservasPorDia->get($day) ?? collect();
                        @endphp
                        <div class="min-h-[140px] p-2 bg-white dark:bg-gray-800 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-750/30 flex flex-col relative group">
                            <!-- Número de día -->
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-black px-2 py-0.5 rounded-full {{ $esHoy ? 'bg-primary-500 text-white shadow-xs ring-2 ring-primary-100 dark:ring-primary-950' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $day }}
                                </span>
                                @if (count($reservasDia) > 0)
                                    <span class="text-[10px] font-bold text-gray-400">
                                        {{ count($reservasDia) }} res.
                                    </span>
                                @endif
                            </div>

                            <!-- Listado de reservaciones del día (SOLO MUESTRA LAS RESERVAS ACTIVAS) -->
                            <div class="space-y-1.5 flex-grow overflow-y-auto max-h-[110px] scrollbar-thin">
                                @forelse ($reservasDia as $r)
                                    @php
                                        $colorClass = match ($r['estado_color']) {
                                            'emerald' => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900/50',
                                            'sky' => 'bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-900/50',
                                            'rose' => 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900/50',
                                            default => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/50',
                                        };
                                        $iconName = !empty($r['habitacion_id']) ? 'heroicon-o-home' : 'heroicon-o-building-office-2';
                                    @endphp
                                    <a
                                        href="/admin/reservas/{{ $r['id'] }}/edit"
                                        class="block text-[10px] leading-tight font-semibold p-1.5 rounded-lg border {{ $colorClass }} hover:scale-[1.02] transition-all duration-150 shadow-2xs truncate cursor-pointer"
                                        title="{{ $r['codigo'] }} - {{ $r['cliente'] }} ({{ $r['recurso_nombre'] }}) · {{ $r['estado'] }}"
                                    >
                                        <div class="flex items-center justify-between gap-1 mb-0.5">
                                            <span class="font-extrabold truncate">
                                                <x-filament::icon :icon="$iconName" class="w-3 h-3 inline-block shrink-0 mr-0.5" />
                                                {{ $r['cliente'] }}
                                            </span>
                                            <span class="font-bold text-[9px] shrink-0">C$ {{ number_format($r['total'], 0) }}</span>
                                        </div>
                                        <div class="text-[9px] opacity-85 truncate">
                                            {{ $r['recurso_nombre'] }} ({{ $r['estado'] }})
                                        </div>
                                    </a>
                                @empty
                                    {{-- Sin reservaciones en este día --}}
                                @endforelse
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

    </div>
</x-filament-panels::page>
