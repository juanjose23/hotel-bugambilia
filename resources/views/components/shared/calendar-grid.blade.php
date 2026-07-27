@props([
    'nombreMes' => '',
    'year' => '',
    'description' => '',
    'days' => [],
    'month' => '',
    'currentYear' => '',
    'items' => null,
    'legend' => [],
    'itemCountLabel' => '',
    'emptyCellMinHeight' => 'min-h-[130px]',
    'itemScrollMaxHeight' => 'max-h-[100px]',
    'itemView' => '',
])

@php
    $today = now();
@endphp

<div class="space-y-6">

    {{-- Cabecera del Calendario con Navegación --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="p-3 bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 rounded-xl shadow-sm ring-1 ring-primary-100/10">
                <x-filament::icon icon="heroicon-o-calendar" class="w-6 h-6" />
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-950 dark:text-white">{{ $nombreMes }} de {{ $year }}</h2>
                @if ($description)
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $description }}</p>
                @endif
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

    {{-- Leyenda de estados --}}
    @if (! empty($legend))
        <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-gray-600 dark:text-gray-400 px-1">
            @foreach ($legend as $entry)
                <span class="flex items-center gap-1.5">
                    <span class="h-3 w-3 rounded-full {{ $entry['color'] }}"></span>
                    {{ $entry['label'] }}
                </span>
            @endforeach
        </div>
    @endif

    {{-- Cuadrícula del Calendario --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">

        {{-- Días de la semana --}}
        <div class="grid grid-cols-7 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20 text-center py-3 font-semibold text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            <div>Lunes</div>
            <div>Martes</div>
            <div>Miércoles</div>
            <div>Jueves</div>
            <div>Viernes</div>
            <div>Sábado</div>
            <div>Domingo</div>
        </div>

        {{-- Celdas de los días --}}
        <div class="grid grid-cols-7 grid-flow-row divide-x divide-y divide-gray-100 dark:divide-gray-700 bg-gray-50/20 dark:bg-gray-900/10">
            @foreach ($days as $day)
                @if ($day === null)
                    <div class="{{ $emptyCellMinHeight }} bg-gray-50/40 dark:bg-gray-900/40 p-2"></div>
                @else
                    @php
                        $esHoy = $day === (int) $today->day && $month == $today->month && $year == $today->year;
                        $dayItems = $items?->get($day) ?? collect();
                    @endphp
                    <div class="{{ $emptyCellMinHeight }} p-2 bg-white dark:bg-gray-800 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-750/30 flex flex-col relative group">

                        {{-- Número de día --}}
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $esHoy ? 'bg-primary-500 text-white shadow-sm ring-2 ring-primary-100 dark:ring-primary-950' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $day }}
                            </span>
                            @if ($itemCountLabel !== '' && count($dayItems) > 0)
                                <span class="text-[10px] font-bold text-gray-400">
                                    {{ count($dayItems) }} {{ $itemCountLabel }}
                                </span>
                            @endif
                        </div>

                        {{-- Listado de items del día --}}
                        <div class="space-y-1.5 flex-grow overflow-y-auto {{ $itemScrollMaxHeight }} scrollbar-thin">
                            @forelse ($dayItems as $item)
                                @if ($itemView)
                                    @include($itemView, ['item' => $item])
                                @endif
                            @empty
                            @endforelse
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
