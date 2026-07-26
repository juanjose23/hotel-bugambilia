@props([
    'estado' => null,
    'size' => 'sm',
])

@php
    $color = 'gray';
    $label = 'Desconocido';
    $icon = 'heroicon-o-question-mark-circle';

    if ($estado instanceof \App\Enums\Restaurante\EstadoPedido) {
        $color = $estado->getColor();
        $label = $estado->getLabel();
        $icon = match($estado) {
            \App\Enums\Restaurante\EstadoPedido::ABIERTO => 'heroicon-o-clock',
            \App\Enums\Restaurante\EstadoPedido::EN_PREPARACION => 'heroicon-o-fire',
            \App\Enums\Restaurante\EstadoPedido::LISTO => 'heroicon-o-check-circle',
            \App\Enums\Restaurante\EstadoPedido::ENTREGADO => 'heroicon-o-check-badge',
            \App\Enums\Restaurante\EstadoPedido::PAGADO => 'heroicon-o-banknotes',
            \App\Enums\Restaurante\EstadoPedido::CANCELADO => 'heroicon-o-x-circle',
        };
    } elseif ($estado instanceof \App\Enums\HabitacionesEspacios\EstadoEspacio) {
        $label = $estado->getLabel();
        $color = match($estado) {
            \App\Enums\HabitacionesEspacios\EstadoEspacio::Disponible => 'success',
            \App\Enums\HabitacionesEspacios\EstadoEspacio::Ocupado => 'danger',
            \App\Enums\HabitacionesEspacios\EstadoEspacio::Limpieza, \App\Enums\HabitacionesEspacios\EstadoEspacio::Sucio => 'warning',
            \App\Enums\HabitacionesEspacios\EstadoEspacio::Reservado => 'info',
            default => 'gray',
        };
        $icon = match($estado) {
            \App\Enums\HabitacionesEspacios\EstadoEspacio::Disponible => 'heroicon-o-check',
            \App\Enums\HabitacionesEspacios\EstadoEspacio::Ocupado => 'heroicon-o-user-group',
            \App\Enums\HabitacionesEspacios\EstadoEspacio::Limpieza, \App\Enums\HabitacionesEspacios\EstadoEspacio::Sucio => 'heroicon-o-sparkles',
            \App\Enums\HabitacionesEspacios\EstadoEspacio::Reservado => 'heroicon-o-bookmark',
            default => 'heroicon-o-wrench-scredriver',
        };
    } elseif (is_string($estado)) {
        $label = ucfirst($estado);
    }
@endphp

<x-filament::badge :color="$color" :icon="$icon" :size="$size">
    {{ $label }}
</x-filament::badge>
