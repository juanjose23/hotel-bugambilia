<table class="data-table">
    <thead>
        <tr>
            <th>Código Inventario</th>
            <th>Producto</th>
            <th>Mantenimiento</th>
            <th style="text-align: center;">Tipo</th>
            <th style="text-align: center;">Fecha Programada</th>
            <th style="text-align: center;">Días Vencido</th>
            <th style="text-align: center;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $mtto)
            @php
                $diasVencido = $mtto->fecha_programada
                    ? (int) now()->diffInDays($mtto->fecha_programada, false) * -1
                    : 0;
            @endphp
            <tr>
                <td>@include('reports.activos.partials.sku', ['codigo' => $mtto->activo?->codigo_inventario ?? '—'])</td>
                <td><strong>{{ $mtto->activo?->producto?->nombre ?? '—' }}</strong></td>
                <td style="font-size:9px;">{{ $mtto->plan?->descripcion ?? '—' }}</td>
                <td style="text-align: center;">{{ $mtto->tipo?->label() ?? '—' }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $mtto->fecha_programada?->format('d/m/Y') ?? '—' }}</td>
                <td style="text-align: center;">
                    <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #991b1b;">
                        {{ $diasVencido }} días
                    </span>
                </td>
                <td style="text-align: center;">
                    @include('reports.activos.partials.estado-activo', ['estado' => $mtto->estado, 'scope' => 'mantenimiento'])
                </td>
            </tr>
        @empty
            @include('reports.activos.partials.empty-state', ['colspan' => 7, 'mensaje' => 'No hay mantenimientos vencidos.'])
        @endforelse
    </tbody>
</table>
