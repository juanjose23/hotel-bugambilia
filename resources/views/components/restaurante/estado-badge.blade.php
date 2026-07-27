@props([
    'estado' => null,
    'size' => 'sm',
])

@php
    $color = 'gray';
    $label = 'Desconocido';
    $icon = 'heroicon-o-question-mark-circle';

    if (is_object($estado)) {
        $label = method_exists($estado, 'getLabel') ? $estado->getLabel() : (string) $estado;
        $color = method_exists($estado, 'getColor') ? $estado->getColor() : 'gray';
        $icon = method_exists($estado, 'getIcon') ? $estado->getIcon() : 'heroicon-o-question-mark-circle';
    } elseif (is_string($estado)) {
        $label = ucfirst($estado);
    }
@endphp

<x-filament::badge :color="$color" :icon="$icon" :size="$size">
    {{ $label }}
</x-filament::badge>
