@props([
    'icon' => 'heroicon-o-check-badge',
    'titulo' => '',
    'subtitulo' => null,
])

@php
    $esHeroicon = is_string($icon)
        && str_starts_with($icon, 'heroicon-');

    $esHugeicon = is_string($icon)
        && str_starts_with($icon, 'hugeicons-');
@endphp

<header
    {{ $attributes->class([
        'flex flex-wrap items-center justify-between gap-4',
        'rounded-2xl border border-gray-200 bg-white p-5 shadow-xs',
        'dark:border-gray-800 dark:bg-gray-900',
    ]) }}
>
    <div class="flex min-w-0 items-center gap-3.5">
        <div
            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl
                   border border-[rgba(107,0,62,0.20)]
                   bg-[rgba(107,0,62,0.10)]
                   text-[#6b003e]
                   dark:border-[rgba(107,0,62,0.30)]
                   dark:bg-[rgba(107,0,62,0.20)]
                   dark:text-[#e87faa]"
        >
            @isset($iconContent)
                {{ $iconContent }}
            @elseif ($esHeroicon)
                <x-filament::icon
                    :icon="$icon"
                    class="h-7 w-7"
                />
            @elseif ($esHugeicon)
                <x-dynamic-component
                    :component="$icon"
                    class="h-7 w-7"
                />
            @else

                <x-filament::icon
                    icon="heroicon-o-check-badge"
                    class="h-7 w-7"
                />
            @endif
        </div>

        <div class="min-w-0">
            <h1
                class="truncate text-xl font-bold uppercase tracking-tight
                       text-gray-950 dark:text-white"
            >
                {{ $titulo }}
            </h1>

            @if (filled($subtitulo))
                <p
                    class="mt-1 text-xs font-medium
                           text-gray-500 dark:text-gray-400"
                >
                    {{ $subtitulo }}
                </p>
            @endif
        </div>
    </div>

    @isset($acciones)
        <div class="flex flex-wrap items-center gap-2">
            {{ $acciones }}
        </div>
    @endisset
</header>
