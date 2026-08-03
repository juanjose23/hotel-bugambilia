@php
    $colorClass = match ($item['estado_color']) {
        'emerald' => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900/50',
        'sky' => 'bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-900/50',
        'rose' => 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900/50',
        default => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/50',
    };
    $iconName = !empty($item['habitacion_id']) ? 'heroicon-o-home' : 'heroicon-o-building-office-2';
@endphp
<div
    class="block text-[10px] leading-tight font-semibold p-1.5 rounded-lg border {{ $colorClass }} transition-colors duration-150 shadow-2xs"
    title="{{ $item['codigo'] }} - {{ $item['cliente'] }} ({{ $item['recurso_nombre'] }}) · {{ $item['estado'] }}"
>
    <div class="flex items-center justify-between gap-1 mb-0.5">
        <span class="font-extrabold truncate">
            <x-filament::icon :icon="$iconName" class="w-3 h-3 inline-block shrink-0 mr-0.5" />
            {{ $item['cliente'] }}
        </span>
        <span class="font-bold text-[9px] shrink-0">C$ {{ number_format($item['total'], 0) }}</span>
    </div>
    <div class="flex items-center gap-1 text-[9px] opacity-85">
        <span class="truncate">{{ $item['recurso_nombre'] }} · {{ $item['estado'] }}</span>
        @if ($item['es_llegada'] ?? false)<span class="shrink-0 rounded bg-white/60 px-1">Entrada</span>@endif
        @if ($item['es_salida'] ?? false)<span class="shrink-0 rounded bg-white/60 px-1">Salida</span>@endif
    </div>
    <div class="mt-1 flex items-center gap-2 border-t border-current/15 pt-1">
        <a href="{{ \App\Filament\Resources\Reservas\ReservaResource::getUrl('view', ['record' => $item['id']]) }}" class="underline underline-offset-2 hover:no-underline">Detalles</a>
        <a href="{{ route('reservas.voucher', ['reserva' => $item['id']]) }}" target="_blank" rel="noopener" class="underline underline-offset-2 hover:no-underline">Imprimir PDF</a>
    </div>
</div>
