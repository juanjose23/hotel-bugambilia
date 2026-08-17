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
    'itemView' => '',
    'availabilityByDay' => [],
])

@php
    $today = now();

    $calendarItems = $items instanceof \Illuminate\Support\Collection
        ? $items
        : collect($items ?? []);

    /*
     * Agrupar por día.
     *
     * El componente espera que $items venga agrupado:
     *
     * [
     *     1 => [...],
     *     2 => [...],
     *     5 => [...],
     * ]
     */
    $itemsByDay = $calendarItems;
    $availability = is_array($availabilityByDay) ? $availabilityByDay : [];
@endphp

<div class="w-full">

    {{-- =========================================================
         CABECERA
    ========================================================== --}}
    <div
        class="mb-5 rounded-lg border border-gray-200 bg-white shadow-sm
               dark:border-gray-700 dark:bg-gray-900"
    >

        <div
            class="flex flex-col gap-4 px-5 py-4
                   sm:flex-row sm:items-center sm:justify-between"
        >

            {{-- Información del calendario --}}
            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center
                           rounded-xl bg-primary-50 text-primary-600
                           dark:bg-primary-950/40 dark:text-primary-400"
                >
                    <x-filament::icon
                        icon="heroicon-o-calendar-days"
                        class="h-6 w-6"
                    />
                </div>

                <div>
                    <h2
                        class="text-lg font-semibold text-gray-950
                               dark:text-white"
                    >
                        {{ $nombreMes }} {{ $year }}
                    </h2>

                    @if ($description)
                        <p
                            class="text-sm text-gray-500
                                   dark:text-gray-400"
                        >
                            {{ $description }}
                        </p>
                    @endif
                </div>

            </div>

            {{-- Navegación --}}
            <div class="flex flex-wrap items-center gap-2">

                <div class="flex items-center gap-1">
                    <select
                        wire:model.live="month"
                        class="rounded-md border-gray-300 bg-white text-xs font-medium text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>

                    <select
                        wire:model.live="year"
                        class="rounded-md border-gray-300 bg-white text-xs font-medium text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        @foreach (range(now()->year - 2, now()->year + 3) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <x-filament::button
                    wire:click="previousMonth"
                    color="gray"
                    icon="heroicon-m-chevron-left"
                    size="sm"
                    tooltip="Mes anterior"
                />

                <x-filament::button
                    wire:click="goToToday"
                    color="gray"
                    size="sm"
                >
                    Hoy
                </x-filament::button>

                <x-filament::button
                    wire:click="nextMonth"
                    color="gray"
                    icon="heroicon-m-chevron-right"
                    size="sm"
                    tooltip="Mes siguiente"
                />

            </div>

        </div>

    </div>


    {{-- =========================================================
         LEYENDA
    ========================================================== --}}
    @if (!empty($legend))

        <div class="mb-4 flex flex-wrap gap-2">

            @foreach ($legend as $entry)

                <div
                    class="flex items-center gap-2 rounded-md
                           border border-gray-200 bg-white
                           px-3 py-1.5 text-xs
                           dark:border-gray-700 dark:bg-gray-900"
                >

                    <span
                        class="h-2.5 w-2.5 rounded-full
                        {{ $entry['color'] ?? 'bg-gray-400' }}"
                    ></span>

                    <span
                        class="text-gray-600 dark:text-gray-300"
                    >
                        {{ $entry['label'] ?? '' }}
                    </span>

                </div>

            @endforeach

        </div>

    @endif


    {{-- =========================================================
         CONTENEDOR PRINCIPAL DEL CALENDARIO

         IMPORTANTE:
         Aquí NO usamos grid de Tailwind.

         Usamos CSS explícito para evitar que Filament/Tailwind
         cambie la estructura.
    ========================================================== --}}
    <div
        class="w-full overflow-x-auto rounded-lg
               border border-gray-200 bg-white shadow-sm ring-1 ring-gray-950/5
               dark:border-gray-700 dark:bg-gray-900 dark:ring-white/5"
    >

        <div
            style="
                min-width: 980px;
                width: 100%;
            "
        >

            {{-- =================================================
                 CABECERA DE LOS DÍAS
            ================================================== --}}
            <div
                style="
                    display: grid;
                    grid-template-columns: repeat(7, minmax(0, 1fr));
                    width: 100%;
                "
                class="border-b border-gray-200
                       bg-gray-50/80
                       dark:border-gray-700
                       dark:bg-gray-900/80"
            >

                @foreach ([
                    'Lunes',
                    'Martes',
                    'Miércoles',
                    'Jueves',
                    'Viernes',
                    'Sábado',
                    'Domingo',
                ] as $diaSemana)

                    <div
                        style="
                            min-width: 0;
                            height: 38px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        "
                        class="
                            border-r border-gray-200
                            text-[11px]
                            font-semibold
                            uppercase
                            text-gray-500

                            last:border-r-0

                            dark:border-gray-700
                            dark:text-gray-400
                        "
                    >
                        {{ $diaSemana }}
                    </div>

                @endforeach

            </div>


            {{-- =================================================
                 CUADRÍCULA REAL DEL CALENDARIO

                 AQUÍ ESTÁ LA PARTE IMPORTANTE.

                 7 columnas SIEMPRE.
            ================================================== --}}
            <div
                role="grid"

                style="
                    display: grid;
                    grid-template-columns: repeat(7, minmax(0, 1fr));
                    width: 100%;
                "

                class="divide-x divide-y divide-gray-200 dark:divide-gray-700"
            >

                @foreach ($days as $index => $day)

                    @php
                        $esFueraDelMes = $day === null;

                        $esFinDeSemana = ($index % 7) >= 5;

                        $esHoy =
                            !$esFueraDelMes
                            && (int) $day === (int) $today->day
                            && (int) $month === (int) $today->month
                            && (int) $year === (int) $today->year;

                        $dayItems = !$esFueraDelMes
                            ? ($itemsByDay->get((int) $day) ?? collect())
                            : collect();

                        $dateKey = !$esFueraDelMes
                            ? sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day)
                            : null;

                        $dayAvailability = $dateKey !== null && isset($availability[$dateKey])
                            ? $availability[$dateKey]
                            : null;

                        $isSoldOut = is_array($dayAvailability) && (bool) ($dayAvailability['agotado'] ?? false);
                        $totalHabitaciones = is_array($dayAvailability)
                            ? max(1, (int) ($dayAvailability['total'] ?? 1))
                            : 1;
                        $ocupadas = is_array($dayAvailability)
                            ? max(0, (int) ($dayAvailability['ocupadas'] ?? 0))
                            : 0;
                        $porcentajeOcupacion = min(100, (int) round(($ocupadas / $totalHabitaciones) * 100));
                    @endphp


                    {{-- =================================================
                         CELDA VACÍA
                    ================================================== --}}
                    @if ($esFueraDelMes)

                        <div
                            role="gridcell"

                            style="
                                min-width: 0;
                                min-height: 150px;
                            "

                            class="bg-gray-50/70 dark:bg-gray-950/50"
                        ></div>

                    @else


                        {{-- =============================================
                             CELDA DEL DÍA
                        ============================================== --}}
                        <div
                            role="gridcell"

                            style="
                                min-width: 0;
                                min-height: 150px;
                                display: flex;
                                flex-direction: column;
                            "

                            class="
                                group
                                relative
                                overflow-hidden
                                p-2.5

                                {{ $isSoldOut
                                    ? 'bg-rose-50/80 dark:bg-rose-950/20'
                                    : ($esFinDeSemana
                                    ? 'bg-gray-50/80 dark:bg-gray-900'
                                    : 'bg-white dark:bg-gray-900')
                                }}

                                {{ $isSoldOut
                                    ? 'hover:bg-rose-50 dark:hover:bg-rose-950/30'
                                    : 'hover:bg-primary-50/40 dark:hover:bg-primary-950/20'
                                }}
                            "
                        >
                            @if (is_array($dayAvailability))
                                <div
                                    class="absolute inset-x-0 top-0 h-1 bg-gray-100 dark:bg-gray-800"
                                >
                                    <div
                                        class="h-full {{ $isSoldOut ? 'bg-rose-500' : 'bg-emerald-500' }}"
                                        style="width: {{ $porcentajeOcupacion }}%;"
                                    ></div>
                                </div>
                            @endif

                            {{-- =====================================
                                 NÚMERO DEL DÍA
                            ====================================== --}}
                            <div
                                class="mb-2.5 flex items-start
                                       justify-between gap-2"
                            >

                                @if ($esHoy)

                                    <div
                                        class="flex h-8 w-8
                                               items-center
                                               justify-center
                                               rounded-md
                                               bg-primary-600
                                               text-sm
                                               font-semibold
                                               text-white
                                               shadow-sm"
                                    >
                                        {{ $day }}
                                    </div>

                                @else

                                    <div
                                        class="
                                            flex h-8 w-8
                                            items-center
                                            justify-center
                                            rounded-md

                                            text-sm
                                            font-semibold

                                            {{ $esFinDeSemana
                                                ? 'text-gray-500 dark:text-gray-400'
                                                : 'text-gray-700 dark:text-gray-200'
                                            }}
                                        "
                                    >
                                        {{ $day }}
                                    </div>

                                @endif


                                @if ($isSoldOut)
                                    <span
                                        class="
                                            inline-flex items-center gap-1 rounded-md
                                            bg-rose-100
                                            px-2
                                            py-1
                                            text-[10px]
                                            font-semibold
                                            text-rose-700

                                            dark:bg-rose-900/40
                                            dark:text-rose-300
                                        "
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                        Agotado
                                    </span>
                                @elseif (is_array($dayAvailability))
                                    <span
                                        class="
                                            inline-flex items-center gap-1 rounded-md
                                            bg-emerald-100
                                            px-2
                                            py-1
                                            text-[10px]
                                            font-semibold
                                            text-emerald-700

                                            dark:bg-emerald-900/40
                                            dark:text-emerald-300
                                        "
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $dayAvailability['disponibles'] ?? 0 }}
                                        disp.
                                    </span>
                                @elseif (
                                    $itemCountLabel !== ''
                                    && count($dayItems) > 0
                                )

                                    <span
                                        class="
                                            rounded-md
                                            bg-gray-100
                                            px-1.5
                                            py-0.5
                                            text-[10px]
                                            font-medium
                                            text-gray-500

                                            dark:bg-gray-800
                                            dark:text-gray-400
                                        "
                                    >
                                        {{ count($dayItems) }}
                                        {{ $itemCountLabel }}
                                    </span>

                                @endif

                            </div>


                            {{-- =====================================
                                 EVENTOS DEL DÍA
                            ====================================== --}}
                            <div
                                style="
                                    min-width: 0;
                                    display: flex;
                                    flex-direction: column;
                                    gap: 6px;
                                "
                            >

                                @foreach ($dayItems as $item)

                                    @if ($itemView)

                                        <div
                                            style="
                                                min-width: 0;
                                                width: 100%;
                                            "
                                        >
                                            @include(
                                                $itemView,
                                                ['item' => $item]
                                            )
                                        </div>

                                    @endif

                                @endforeach

                            </div>

                            @if (is_array($dayAvailability))
                                <div class="mt-auto pt-2">
                                    <div class="flex items-center justify-between text-[10px] font-medium text-gray-500 dark:text-gray-400">
                                        <span>{{ $ocupadas }} ocupadas</span>
                                        <span>{{ $totalHabitaciones }} total</span>
                                    </div>
                                </div>
                            @endif

                        </div>

                    @endif

                @endforeach

            </div>

        </div>

    </div>

</div>
