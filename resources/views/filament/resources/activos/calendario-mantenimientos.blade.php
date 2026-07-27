<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Cabecera del Calendario con Navegación -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 rounded-xl shadow-sm ring-1 ring-primary-100/10">
                    <x-filament::icon icon="heroicon-o-calendar" class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-950 dark:text-white">{{ $nombreMes }} de {{ $year }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Control visual de órdenes de mantenimiento preventivo y correctivo.</p>
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

        <!-- Cuadrícula del Calendario -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <!-- Días de la semana -->
            <div class="grid grid-cols-7 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20 text-center py-3 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
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
                @foreach ($days as $day)
                    @if ($day === null)
                        <!-- Celda vacía para días del mes anterior -->
                        <div class="min-h-[120px] bg-gray-50/40 dark:bg-gray-900/40 p-2"></div>
                    @else
                        @php
                            $esHoy = $day === (int) now()->day && $month === (int) now()->month && $year === (int) now()->year;
                            $mantsDia = $mantenimientos->get($day) ?? collect();
                        @endphp
                        <div class="min-h-[130px] p-2 bg-white dark:bg-gray-800 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-750/30 flex flex-col relative group">
                            <!-- Número de día -->
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $esHoy ? 'bg-primary-500 text-white shadow-sm ring-2 ring-primary-100 dark:ring-primary-950' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $day }}
                                </span>
                            </div>

                            <!-- Listado de mantenimientos del día -->
                            <div class="space-y-1.5 flex-grow overflow-y-auto max-h-[85px] scrollbar-thin">
                                @foreach ($mantsDia as $m)
                                    @php
                                        $color = match ($m->estado?->value) {
                                            2 => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-950/30 dark:text-blue-300 dark:border-blue-900/50', // En Proceso
                                            3 => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-300 dark:border-emerald-900/50', // Completado
                                            4 => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-950/30 dark:text-rose-300 dark:border-rose-900/50', // Cancelado
                                            default => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-900/50', // Programado / Default
                                        };
                                        $tipoIcon = match ($m->tipo?->value) {
                                            'correctivo' => 'heroicon-o-wrench',
                                            'preventivo' => 'heroicon-o-calendar-days',
                                            'garantia' => 'heroicon-o-shield-check',
                                            default => 'heroicon-o-cog-6-tooth',
                                        };
                                    @endphp
                                    <a
                                        href="{{ \App\Filament\Resources\Activos\ActivoMantenimiento\ActivoMantenimientoResource::getUrl('view', ['record' => $m->id]) }}"
                                        class="block text-[10px] leading-tight font-medium p-1.5 rounded-lg border {{ $color }} hover:scale-[1.02] transition-all duration-150 shadow-xs truncate cursor-pointer hover:shadow-sm"
                                        title="{{ $m->tipo?->getLabel() }}: {{ $m->activo?->nombre_descriptivo ?? 'Sin activo' }} ({{ $m->estado?->getLabel() }})"
                                    >
                                        <x-filament::icon :icon="$tipoIcon" class="w-3.5 h-3.5 mr-1 inline-block shrink-0" />
                                        <span class="font-semibold">{{ $m->activo?->nombre_descriptivo ?? 'Sin activo' }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
