@php
    $color = match ($item->estado?->value) {
        2 => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-950/30 dark:text-blue-300 dark:border-blue-900/50',
        3 => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-300 dark:border-emerald-900/50',
        4 => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-950/30 dark:text-rose-300 dark:border-rose-900/50',
        default => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-900/50',
    };
    $tipoIcon = match ($item->tipo?->value) {
        'correctivo' => 'heroicon-o-wrench',
        'preventivo' => 'heroicon-o-calendar-days',
        'garantia' => 'heroicon-o-shield-check',
        default => 'heroicon-o-cog-6-tooth',
    };
@endphp
<a
    href="{{ \App\Filament\Resources\Activos\ActivoMantenimiento\ActivoMantenimientoResource::getUrl('view', ['record' => $item->id]) }}"
    class="block text-[10px] leading-tight font-medium p-1.5 rounded-lg border {{ $color }} hover:scale-[1.02] transition-all duration-150 shadow-xs truncate cursor-pointer hover:shadow-sm"
    title="{{ $item->tipo?->getLabel() }}: {{ $item->activo?->nombre_descriptivo ?? 'Sin activo' }} ({{ $item->estado?->getLabel() }})"
>
    <x-filament::icon :icon="$tipoIcon" class="w-3.5 h-3.5 mr-1 inline-block shrink-0" />
    <span class="font-semibold">{{ $item->activo?->nombre_descriptivo ?? 'Sin activo' }}</span>
</a>
