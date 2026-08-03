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
@endphp

<div class="w-full">

    {{-- =========================================================
         CABECERA
    ========================================================== --}}
    <div
        class="mb-5 rounded-xl border border-gray-200 bg-white shadow-sm
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
            <div class="flex items-center gap-1">

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
                    class="flex items-center gap-2 rounded-lg
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
        class="w-full overflow-x-auto rounded-xl
               border border-gray-300 shadow-sm
               dark:border-gray-700"
    >

        <div
            style="
                min-width: 900px;
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
                class="border-b border-gray-300
                       bg-gray-50
                       dark:border-gray-700
                       dark:bg-gray-900"
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
                            height: 42px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        "
                        class="
                            border-r border-gray-200
                            text-[11px]
                            font-semibold
                            uppercase
                            tracking-wide
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
                    gap: 1px;
                    background-color: rgb(209 213 219);
                "

                class="dark:bg-gray-700"
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
                    @endphp


                    {{-- =================================================
                         CELDA VACÍA
                    ================================================== --}}
                    @if ($esFueraDelMes)

                        <div
                            role="gridcell"

                            style="
                                min-width: 0;
                                min-height: 175px;
                                background: rgb(249 250 251);
                            "

                            class="dark:bg-gray-950"
                        ></div>

                    @else


                        {{-- =============================================
                             CELDA DEL DÍA
                        ============================================== --}}
                        <div
                            role="gridcell"

                            style="
                                min-width: 0;
                                min-height: 175px;
                                display: flex;
                                flex-direction: column;
                            "

                            class="
                                group
                                relative
                                p-2

                                {{ $esFinDeSemana
                                    ? 'bg-gray-50 dark:bg-gray-900'
                                    : 'bg-white dark:bg-gray-900'
                                }}

                                hover:bg-primary-50/30
                                dark:hover:bg-primary-950/20
                            "
                        >

                            {{-- =====================================
                                 NÚMERO DEL DÍA
                            ====================================== --}}
                            <div
                                class="mb-2 flex items-center
                                       justify-between"
                            >

                                @if ($esHoy)

                                    <div
                                        class="flex h-8 w-8
                                               items-center
                                               justify-center
                                               rounded-full
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
                                            rounded-full

                                            text-sm
                                            font-medium

                                            {{ $esFinDeSemana
                                                ? 'text-gray-500 dark:text-gray-400'
                                                : 'text-gray-700 dark:text-gray-200'
                                            }}
                                        "
                                    >
                                        {{ $day }}
                                    </div>

                                @endif


                                {{-- Cantidad --}}
                                @if (
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
                                    gap: 4px;
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

                        </div>

                    @endif

                @endforeach

            </div>

        </div>

    </div>

</div>
