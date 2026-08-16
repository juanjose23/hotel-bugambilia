@php
    $colorClass = match ($item['estado_color']) {
        'emerald' => 'border-l-emerald-500 bg-emerald-500/10 text-emerald-950 dark:bg-emerald-950/40 dark:text-emerald-200 hover:bg-emerald-500/20',
        'sky' => 'border-l-sky-500 bg-sky-500/10 text-sky-950 dark:bg-sky-950/40 dark:text-sky-200 hover:bg-sky-500/20',
        'rose' => 'border-l-rose-400 bg-rose-500/5 text-gray-500 line-through dark:bg-rose-950/20 dark:text-gray-400 hover:bg-rose-500/10 opacity-70 hover:opacity-100',
        default => 'border-l-amber-500 bg-amber-500/10 text-amber-950 dark:bg-amber-950/40 dark:text-amber-200 hover:bg-amber-500/20',
    };

    $badgeDot = match ($item['estado_color']) {
        'emerald' => 'bg-emerald-500',
        'sky' => 'bg-sky-500',
        'rose' => 'bg-rose-400',
        default => 'bg-amber-500',
    };

    $tooltip = "{$item['codigo']} · {$item['cliente']} ({$item['recurso_nombre']}) · {$item['estado']} · Total: C$ " . number_format($item['total'], 0);
@endphp

<a
    href="{{ \App\Filament\Resources\Reservas\ReservaResource::getUrl('view', ['record' => $item['id']]) }}"
    title="{{ $tooltip }}"
    class="group/card flex min-w-0 items-center justify-between gap-1.5 rounded-lg border-l-3 px-2 py-1 text-[10px] font-medium transition-all shadow-2xs hover:shadow-xs {{ $colorClass }}"
>
    {{-- Izquierda: Indicador de estado + Nombre recurso / Cliente --}}
    <div class="flex min-w-0 flex-1 items-center gap-1.5 truncate">
        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $badgeDot }}"></span>
        <span class="truncate font-bold tracking-tight text-gray-900 dark:text-white">
            {{ $item['recurso_nombre'] !== '—' ? $item['recurso_nombre'] : $item['codigo'] }}
        </span>
        <span class="hidden truncate text-gray-600 dark:text-gray-300 sm:inline">
            · {{ $item['cliente'] }}
        </span>
    </div>

    {{-- Derecha: Badges Entrada / Salida + Precio --}}
    <div class="flex shrink-0 items-center gap-1 text-[9px]">
        @if ($item['es_llegada'] ?? false)
            <span class="rounded bg-emerald-600 px-1 py-0.2 font-black text-white uppercase dark:bg-emerald-500">In</span>
        @endif
        @if ($item['es_salida'] ?? false)
            <span class="rounded bg-gray-500 px-1 py-0.2 font-black text-white uppercase dark:bg-gray-600">Out</span>
        @endif
        <span class="font-extrabold tracking-tight opacity-90">
            C${{ number_format($item['total'], 0) }}
        </span>
    </div>
</a>
