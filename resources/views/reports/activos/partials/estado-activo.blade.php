@php
    $estadosActivos = [
        1 => ['color' => '#065f46', 'bg' => '#d1fae5'],
        2 => ['color' => '#92400e', 'bg' => '#fef3c7'],
        3 => ['color' => '#991b1b', 'bg' => '#fee2e2'],
        4 => ['color' => '#991b1b', 'bg' => '#fee2e2'],
        5 => ['color' => '#1e40af', 'bg' => '#dbeafe'],
        6 => ['color' => '#6b7280', 'bg' => '#f3f4f6'],
    ];

    $estadosMantenimiento = [
        1 => ['color' => '#92400e', 'bg' => '#fef3c7'],
        2 => ['color' => '#1e40af', 'bg' => '#dbeafe'],
        3 => ['color' => '#065f46', 'bg' => '#d1fae5'],
        4 => ['color' => '#991b1b', 'bg' => '#fee2e2'],
    ];

    $mapa = ($scope ?? 'activo') === 'mantenimiento' ? $estadosMantenimiento : $estadosActivos;
    $valor = $estado?->value ?? null;
    $colores = $mapa[$valor] ?? ['color' => '#6b7280', 'bg' => '#f3f4f6'];
@endphp
<span class="badge" @isset($cssClass) class="{{ $cssClass }}" @endisset style="background:{{ $colores['bg'] }};color:{{ $colores['color'] }};border:1px solid {{ $colores['color'] }};{{ $style ?? '' }}">
    {{ $estado?->label() ?? '—' }}
</span>
