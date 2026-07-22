@props([
    'checklist' => [],
])

<div class="mt-2 space-y-2">
    @foreach($checklist as $task => $completed)
        @php
            $class = $completed ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500 line-through';
        @endphp
        <div class="flex items-center text-sm">
            <span class="mr-2">
                @if($completed)
                    <svg class="w-4 h-4 text-emerald-500 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                @else
                    <svg class="w-4 h-4 text-red-500 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                @endif
            </span>
            <span class="{{ $class }}">{{ $task }}</span>
        </div>
    @endforeach
</div>
