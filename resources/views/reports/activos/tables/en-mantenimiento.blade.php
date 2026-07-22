<table class="data-table">
    <thead>
        <tr>
            <th>Código Inventario</th>
            <th>Producto</th>
            <th>Mantenimiento</th>
            <th style="text-align: center;">Tipo</th>
            <th>Proveedor</th>
            <th style="text-align: center;">Fecha Programada</th>
            <th style="text-align: center;">Estado Mtto.</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $activo)
            @foreach($activo->mantenimientos as $mtto)
                <tr>
                    <td>@include('reports.activos.partials.sku', ['codigo' => $activo->codigo_inventario])</td>
                    <td><strong>{{ $activo->producto?->nombre ?? '—' }}</strong></td>
                    <td style="font-size:9px;">{{ $mtto->plan?->descripcion ?? '—' }}</td>
                    <td style="text-align: center;">{{ $mtto->tipo?->label() ?? '—' }}</td>
                    <td style="font-size:9px;">{{ $mtto->plan?->proveedor?->persona?->primer_nombre ?? 'Taller interno' }}</td>
                    <td style="text-align: center;">{{ $mtto->fecha_programada?->format('d/m/Y') ?? '—' }}</td>
                    <td style="text-align: center;">
                        @include('reports.activos.partials.estado-activo', ['estado' => $mtto->estado, 'scope' => 'mantenimiento'])
                    </td>
                </tr>
            @endforeach
        @empty
            @include('reports.activos.partials.empty-state', ['colspan' => 7, 'mensaje' => 'No hay activos actualmente en mantenimiento.'])
        @endforelse
    </tbody>
</table>
