@props([
    'value' => '',
    'label' => '',
    'color' => 'bg-gray-100 dark:bg-gray-800/50',
    'valueColor' => 'text-gray-500 dark:text-gray-400',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center p-3 sm:p-4 rounded-xl ' . $color]) }}>
    <div class="text-2xl sm:text-3xl font-extrabold tabular-nums {{ $valueColor }}">
        {{ $value }}
    </div>
    <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium uppercase tracking-wide text-center">
        {{ $label }}
    </div>
</div>
