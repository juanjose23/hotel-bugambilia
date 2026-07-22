@props([
    'type' => 'warning',
])

@php
    $classes = match ($type) {
        'complete' => 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300',
        'missing' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
        'warning' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    };
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
    {{ $slot }}
</span>
