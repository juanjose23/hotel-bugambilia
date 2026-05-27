<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 16px; color: #711C37;">HOTEL BUGAMBILIAS</th>
        </tr>
        <tr>
            <th colspan="7" style="font-style: italic; font-size: 12px;">Reporte de Inventario de Activos Fijos</th>
        </tr>
        <tr>
            <th colspan="7" style="font-size: 10px;">Fecha de Generación: {{ $fecha }}</th>
        </tr>
        <tr></tr>
        <tr style="background-color: #711C37; color: #ffffff; font-weight: bold;">
            <th>Código de Inventario</th>
            <th>Nombre Descriptivo</th>
            <th>Tipo de Activo</th>
            <th>Número de Serie</th>
            <th>Ubicación Actual</th>
            <th>Costo de Adquisición</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($activos as $activo)
        <tr>
            <td style="font-weight: bold;">{{ $activo->codigo_inventario }}</td>
            <td>{{ $activo->nombre_descriptivo }}</td>
            <td>{{ $activo->producto?->nombre ?? 'N/A' }}</td>
            <td>{{ $activo->numero_serie ?: '—' }}</td>
            <td>
                @if($activo->asignacionActiva?->asignable)
                    @php
                        $tipo = class_basename($activo->asignacionActiva->asignable_type);
                        $prefijo = match ($tipo) {
                            'Habitacion' => 'Hab.',
                            'Ubicacion' => 'Ubic.',
                            'Espacio' => 'Esp.',
                            default => $tipo,
                        };
                    @endphp
                    {{ $prefijo }} {{ $activo->asignacionActiva->asignable->nombre }}
                @else
                    Sin asignar
                @endif
            </td>
            <td>
                @if($activo->costo_adquisicion !== null)
                    {{ number_format($activo->costo_adquisicion, 2) }}
                @else
                    0.00
                @endif
            </td>
            <td>{{ $activo->estado?->label() ?? 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
