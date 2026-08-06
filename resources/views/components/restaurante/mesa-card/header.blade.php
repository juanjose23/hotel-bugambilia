@props([
    'mesa',
    'configuracionTipo',
    'capacidad',
    'estadoActualEnum',
])

@php
    $estadoVal = is_object($mesa->estado) ? (int) $mesa->estado->value : (int) $mesa->estado;
    $rawMeta = match (true) {
        is_array($mesa->meta_datos) => $mesa->meta_datos,
        is_string($mesa->meta_datos) && trim($mesa->meta_datos) !== '' => json_decode($mesa->meta_datos, true),
        default => [],
    };
    $meta = is_array($rawMeta) ? $rawMeta : [];
    $nombreCliente = is_string($meta['nombre_cliente'] ?? null) ? $meta['nombre_cliente'] : null;
    $codigoReserva = is_string($meta['codigo_reserva'] ?? null) ? $meta['codigo_reserva'] : null;
    $numeroHabitacion = $meta['numero_habitacion'] ?? $meta['habitacion'] ?? null;

    $primerPedido = $mesa->relationLoaded('pedidosActivos')
        ? $mesa->pedidosActivos
            ->filter(static fn ($pedido): bool => $pedido->abierto_en !== null || $pedido->created_at !== null)
            ->sortBy(static fn ($pedido) => $pedido->abierto_en ?? $pedido->created_at)
            ->first()
        : null;

    $inicioAtencion = $primerPedido?->abierto_en ?? $primerPedido?->created_at;
    $minutosTranscurridos = $inicioAtencion !== null
        ? (int) $inicioAtencion->diffInMinutes(now(), true)
        : null;
    $duracion = match (true) {
        $minutosTranscurridos === null => null,
        $minutosTranscurridos >= 1440 => '+24 h',
        $minutosTranscurridos >= 60 => sprintf('%dh %02dm', intdiv($minutosTranscurridos, 60), $minutosTranscurridos % 60),
        default => sprintf('%d min', $minutosTranscurridos),
    };
@endphp

<header class="mb-3 flex flex-col gap-2.5 border-b border-gray-200/80 pb-3 dark:border-gray-800/80 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex items-center gap-2.5 min-w-0">
        {{-- Icono del tipo de mesa --}}
        <div
            @class([
                'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border shadow-2xs',
                ...$configuracionTipo['iconoFondo'],
                ...$configuracionTipo['borde'],
            ])
        >
            <x-filament::icon
                :icon="$configuracionTipo['icono']"
                @class([
                    'h-4.5 w-4.5',
                    ...$configuracionTipo['iconoColor'],
                ])
            />
        </div>

        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <h3 dusk="mesa-{{ $mesa->id }}-nombre" class="truncate text-base font-black tracking-tight text-gray-950 dark:text-white">
                    {{ $mesa->nombre }}
                </h3>

                <span
                    @class([
                        'inline-flex rounded-md px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset',
                        ...$configuracionTipo['etiqueta'],
                    ])
                >
                    {{ $configuracionTipo['label'] }}
                </span>
            </div>

            @if ($nombreCliente)
                <p class="mt-0.5 flex items-center gap-1 truncate text-xs font-semibold text-gray-700 dark:text-gray-300">
                    <x-filament::icon icon="heroicon-o-user" class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                    <span class="truncate">{{ $nombreCliente }}</span>
                </p>
            @else
                <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                    Mesa #{{ $mesa->id }} &middot; {{ $capacidad }} personas
                </p>
            @endif
        </div>
    </div>

    <div class="flex shrink-0 items-center self-start">
        @if ($estadoActualEnum)
            <x-filament::badge
                dusk="mesa-{{ $mesa->id }}-estado"
                :color="$estadoActualEnum->getColor()"
                :icon="$estadoActualEnum->getIcon()"
                size="sm"
            >
                {{ $estadoActualEnum->getLabel() }}
            </x-filament::badge>
        @else
            <x-filament::badge color="gray" size="sm">
                Sin estado
            </x-filament::badge>
        @endif
    </div>
</header>

<dl class="mb-2.5 grid grid-cols-2 gap-2" aria-label="Información clave de la mesa">
    <div class="flex min-w-0 items-center gap-2 rounded-xl bg-gray-50 px-2.5 py-1.5 border border-gray-100 dark:bg-gray-800/60 dark:border-gray-800">
        <x-filament::icon icon="heroicon-o-clock" class="h-4 w-4 shrink-0 text-gray-400" />
        <div class="min-w-0">
            <dt class="text-[9px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Tiempo</dt>
            <dd @class([
                'text-xs font-bold truncate',
                'text-amber-600 dark:text-amber-400' => $minutosTranscurridos !== null && $minutosTranscurridos >= 1440,
                'text-gray-800 dark:text-gray-200' => $minutosTranscurridos === null || $minutosTranscurridos < 1440,
            ])>{{ $duracion ?? 'Sin atención' }}</dd>
        </div>
    </div>

    <div class="flex min-w-0 items-center gap-2 rounded-xl bg-gray-50 px-2.5 py-1.5 border border-gray-100 dark:bg-gray-800/60 dark:border-gray-800">
        <x-filament::icon icon="heroicon-o-bookmark-square" class="h-4 w-4 shrink-0 text-gray-400" />
        <div class="min-w-0">
            <dt class="text-[9px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Referencia</dt>
            <dd class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">
                @if ($numeroHabitacion)
                    Hab. {{ $numeroHabitacion }}
                @elseif ($codigoReserva)
                    Reserva {{ $codigoReserva }}
                @else
                    Libre
                @endif
            </dd>
        </div>
    </div>
</dl>

