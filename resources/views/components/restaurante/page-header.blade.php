@props([
    'icon' => 'heroicon-o-sparkles',
    'title' => '',
    'subtitle' => null,
])

<div class="flex flex-wrap items-center justify-between gap-4 p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs">
    <div class="flex items-center gap-3.5">
        <div class="p-3 bg-[rgba(107,0,62,0.1)] dark:bg-[rgba(107,0,62,0.2)] text-[#6b003e] dark:text-[#e87faa] rounded-xl border border-[rgba(107,0,62,0.2)] dark:border-[rgba(107,0,62,0.3)] shrink-0">
            <x-filament::icon :icon="$icon" class="w-6 h-6" />
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-950 dark:text-white tracking-tight uppercase">
                {{ $title }}
            </h1>
            @if($subtitle)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    </div>

    @if(isset($actions))
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
