<table class="data-table">
    <thead>
        <tr>
            <th>Código Inventario</th>
            <th>Producto</th>
            <th style="text-align: center;">Estado</th>
            <th>Ubicación Actual</th>
            <th style="text-align: right;">Costo Adquisición</th>
            <th style="text-align: center;">Fecha Adquisición</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $activo)
            <tr>
                <td>@include('reports.activos.partials.sku', ['codigo' => $activo->codigo_inventario])</td>
                <td>
                    <strong>{{ $activo->producto?->nombre ?? '—' }}</strong>
                    @if($activo->variante)
                        <br><span style="font-size:8px;color:#666;">{{ $activo->variante->codigo }}</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    @include('reports.activos.partials.estado-activo', ['estado' => $activo->estado])
                </td>
                <td style="font-size:9px;">{{ $activo->asignacionActiva?->destinoLabel() ?? 'Sin asignar' }}</td>
                <td style="text-align: right;">
                    @include('reports.activos.partials.costo', ['monto' => $activo->costo_adquisicion, 'monedaSimbolo' => $activo->moneda?->simbolo])
                </td>
                <td style="text-align: center;">{{ $activo->fecha_adquisicion?->format('d/m/Y') ?? '—' }}</td>
            </tr>
        @empty
            @include('reports.activos.partials.empty-state', ['colspan' => 6, 'mensaje' => 'No se encontraron activos con los filtros aplicados.'])
        @endforelse
    </tbody>
    @if(($esUltimaPagina ?? false) && count($items) > 0)
        @include('reports.activos.partials.table-total', [
            'labelColspan' => 4,
            'label' => 'Total General:',
            'total' => $totalCosto ?? 0,
            'monedaSimbolo' => 'C$',
            'count' => ($totalRegistros ?? count($items)) . ' activos',
        ])
    @endif
</table>
