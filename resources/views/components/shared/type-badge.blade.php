@use('App\Models\Habitaciones\Habitacion')
@use('App\Models\Espacios\Espacio')
@use('App\Models\Catalogos\Ubicacion')

@props([
    'type' => '',
])

@php
    $label = match ($type) {
        Habitacion::class => 'Habitación',
        Espacio::class => 'Espacio Común',
        Ubicacion::class => 'Ubicación Física',
        default => 'Otro',
    };

    $classes = match ($type) {
        Habitacion::class => 'bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
        Espacio::class => 'bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
        Ubicacion::class => 'bg-teal-50 dark:bg-teal-950 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800',
        default => 'bg-gray-50 text-gray-700 border-gray-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'text-[10px] px-2 py-0.5 font-medium rounded-md border ' . $classes]) }}>
    {{ $label }}
</span>
