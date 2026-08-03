@props([
    'mesa',
    'configuracionTipo',
    'capacidad',
    'estilo',
    'tienePedidos',
    'cantidadPedidos',
])

@php
    $estadoActual = is_object($mesa->estado)
        ? (int) $mesa->estado->value
        : (int) $mesa->estado;

    // Paleta de colores visuales por estado estilo POS Floor Plan
    $estilosEstado = match ($estadoActual) {
        1 => [ // Disponible
            'mesaBg' => 'bg-emerald-600 dark:bg-emerald-500 text-white shadow-emerald-600/30',
            'sillaBg' => 'bg-emerald-100 border-emerald-400 dark:bg-emerald-950/80 dark:border-emerald-600/80',
            'badgeBg' => 'bg-emerald-700 text-white',
            'resplandor' => 'shadow-[0_0_20px_rgba(16,185,129,0.25)]',
        ],
        5 => [ // Ocupado
            'mesaBg' => 'bg-rose-600 dark:bg-rose-500 text-white shadow-rose-600/30',
            'sillaBg' => 'bg-rose-100 border-rose-400 dark:bg-rose-950/80 dark:border-rose-600/80',
            'badgeBg' => 'bg-rose-700 text-white',
            'resplandor' => 'shadow-[0_0_20px_rgba(244,63,94,0.25)]',
        ],
        4 => [ // Reservado
            'mesaBg' => 'bg-sky-600 dark:bg-sky-500 text-white shadow-sky-600/30',
            'sillaBg' => 'bg-sky-100 border-sky-400 dark:bg-sky-950/80 dark:border-sky-600/80',
            'badgeBg' => 'bg-sky-700 text-white',
            'resplandor' => 'shadow-[0_0_20px_rgba(14,165,233,0.25)]',
        ],
        3, 6 => [ // Sucio / Limpieza
            'mesaBg' => 'bg-amber-500 dark:bg-amber-600 text-white shadow-amber-500/30',
            'sillaBg' => 'bg-amber-100 border-amber-400 dark:bg-amber-950/80 dark:border-amber-600/80',
            'badgeBg' => 'bg-amber-700 text-white',
            'resplandor' => 'shadow-[0_0_20px_rgba(245,158,11,0.25)]',
        ],
        default => [ // Mantenimiento / Inactivo
            'mesaBg' => 'bg-slate-400 dark:bg-slate-600 text-white shadow-slate-400/20',
            'sillaBg' => 'bg-slate-100 border-slate-300 dark:bg-slate-900 dark:border-slate-700',
            'badgeBg' => 'bg-slate-700 text-white',
            'resplandor' => '',
        ],
    };

    $numSillas = min(10, max(2, $capacidad));
    $tipo = strtolower(trim((string) ($configuracionTipo['label'] ?? 'redonda')));
    $esRedonda = str_contains($tipo, 'redonda');
    $esBarra = str_contains($tipo, 'barra');
@endphp

<div class="relative flex min-h-[140px] w-full flex-col items-center justify-center py-3 select-none" aria-label="Plano visual de {{ $mesa->nombre }}">
    {{-- Fondo decorativo sutil estilo plano de restaurant --}}
    <div class="relative flex items-center justify-center w-full max-w-[220px]">
        {{-- Sillas alrededor (Floorplan Stools/Chairs) --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            @if ($esBarra)
                {{-- Distribución lineal para barra --}}
                <div class="flex items-center justify-around w-full px-2 -mt-16">
                    @for ($i = 0; $i < $numSillas; $i++)
                        <div class="h-4 w-4 rounded-full border-2 {{ $estilosEstado['sillaBg'] }} shadow-xs"></div>
                    @endfor
                </div>
            @else
                {{-- Distribución perimetral (Circular/Rectangular) --}}
                @for ($i = 0; $i < $numSillas; $i++)
                    @php
                        $angulo = ($i * (360 / $numSillas)) - 90;
                        $rad = deg2rad($angulo);
                        $distanciaX = $esRedonda ? 48 : 56;
                        $distanciaY = $esRedonda ? 48 : 40;
                        $posX = cos($rad) * $distanciaX;
                        $posY = sin($rad) * $distanciaY;
                    @endphp
                    <div
                        class="absolute h-4 w-4 rounded-full border-2 {{ $estilosEstado['sillaBg'] }} transition-transform duration-300 shadow-xs"
                        style="transform: translate({{ $posX }}px, {{ $posY }}px);"
                    ></div>
                @endfor
            @endif
        </div>

        {{-- Mesa central estilizada estilo POS Floorplan --}}
        <div
            @class([
                'relative z-10 flex flex-col items-center justify-center p-3 text-center transition-all duration-300 border-2 border-white/30 dark:border-gray-900/40 shadow-lg',
                $estilosEstado['mesaBg'],
                $estilosEstado['resplandor'],
                'rounded-full h-24 w-24' => $esRedonda,
                'rounded-2xl h-20 w-32' => ! $esRedonda && ! $esBarra,
                'rounded-xl h-14 w-44' => $esBarra,
            ])
        >
            <div class="flex items-center justify-center gap-1">
                <x-filament::icon
                    :icon="$configuracionTipo['icono']"
                    class="h-4 w-4 text-white/90 drop-shadow-xs"
                />
                <span class="text-xs font-black uppercase tracking-wider text-white">
                    {{ $mesa->nombre }}
                </span>
            </div>

            <div class="mt-0.5 flex items-center gap-1 text-[10px] font-semibold text-white/80">
                <x-filament::icon icon="heroicon-m-user-group" class="h-3 w-3" />
                <span>{{ $capacidad }} pers.</span>
            </div>
        </div>

        {{-- Badge flotante con el número de comandas activas --}}
        @if ($tienePedidos)
            <div class="absolute -right-1 -top-1 z-20 flex h-7 min-w-7 items-center justify-center rounded-full bg-stone-950 px-1.5 text-xs font-black text-white shadow-md ring-2 ring-white dark:bg-white dark:text-stone-950 dark:ring-stone-900">
                <span>{{ $cantidadPedidos }}</span>
            </div>
        @endif
    </div>
</div>

