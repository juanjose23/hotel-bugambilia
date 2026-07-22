@props([
    'percentage' => 0,
    'completed' => 0,
    'total' => 0,
])

@php
    $barColor = $percentage === 100 ? 'bg-emerald-500' : 'bg-primary-500';
@endphp

<div class="flex items-center gap-3 w-full max-w-md mt-1">
    <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-2.5">
        <div class="{{ $barColor }} h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
    </div>
    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $completed }}/{{ $total }} ({{ $percentage }}%)</span>
</div>
