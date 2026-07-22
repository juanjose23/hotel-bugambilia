<table class="data-table">
    <thead>
        <tr>
            <th>Código Inventario</th>
            <th>Producto</th>
            <th style="text-align: center;">Estado</th>
            <th style="text-align: right;">Costo Adquisición</th>
            <th style="text-align: center;">Moneda</th>
            <th>Proveedor</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $activo)
            <tr>
                <td>@include('reports.activos.partials.sku', ['codigo' => $activo->codigo_inventario])</td>
                <td><strong>{{ $activo->producto?->nombre ?? '—' }}</strong></td>
                <td style="text-align: center;">
                    @include('reports.activos.partials.estado-activo', ['estado' => $activo->estado])
                </td>
                <td style="text-align: right;">
                    @include('reports.activos.partials.costo', ['monto' => $activo->costo_adquisicion, 'monedaSimbolo' => $activo->moneda?->simbolo])
                </td>
                <td style="text-align: center;">{{ $activo->moneda?->codigo ?? '—' }}</td>
                <td style="font-size:9px;">{{ $activo->proveedor?->persona?->primer_nombre ?? '—' }}</td>
            </tr>
        @empty
            @include('reports.activos.partials.empty-state', ['colspan' => 6, 'mensaje' => 'Todos los activos tienen asignación actual.'])
        @endforelse
    </tbody>
</table>
