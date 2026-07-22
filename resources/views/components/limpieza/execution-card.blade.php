@props([
    'accentColor' => 'gray',
    'titulo' => '',
    'tipo' => '',
    'url' => '',
    'ejecucionId' => null,
])

@php
    $accentMap = [
        'amber' => 'border-amber-200 dark:border-amber-900/50 bg-amber-500',
        'blue' => 'border-blue-200 dark:border-blue-900/50 bg-blue-500',
        'green' => 'border-gray-200 dark:border-gray-800 bg-green-500',
        'red' => 'border-red-200 dark:border-red-900/50 bg-red-500',
    ];
    $barColor = $accentMap[$accentColor][1] ?? 'bg-gray-500';
    $borderColor = $accentMap[$accentColor][0] ?? 'border-gray-200 dark:border-gray-800';
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-950 p-4 rounded-xl border shadow-sm relative overflow-hidden ' . $borderColor]) }}>
    <div class="absolute top-0 right-0 w-1.5 h-full {{ $barColor }}"></div>

    {{-- Header --}}
    <div class="flex items-start justify-between gap-2 mb-2">
        <h5 class="font-bold text-gray-950 dark:text-white text-base">
            <a href="{{ $url }}"
               class="hover:text-primary-600 dark:hover:text-primary-400 hover:underline transition-colors flex items-center gap-1.5">
                {{ $titulo }}
                <x-heroicon-o-information-circle class="w-4 h-4 text-gray-400 hover:text-primary-500 transition-colors" />
            </a>
        </h5>
        <x-shared.type-badge :type="$tipo" />
    </div>

    {{-- Body slot --}}
    <div class="space-y-1.5 text-xs text-gray-500 dark:text-gray-400 mb-4">
        {{ $slot }}
    </div>

    {{-- Footer slot --}}
    @if (isset($footer))
        <div>
            {{ $footer }}
        </div>
    @endif
</div>
