<table class="data-table">
    <thead>
        <tr>
            <th>Código Inventario</th>
            <th>Producto</th>
            <th style="text-align: right;">Costo Adquisición</th>
            <th style="text-align: center;">Moneda</th>
            <th>Última Ubicación Conocida</th>
            <th>Última Asignación</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $activo)
            @php $ultimaAsignacion = $activo->asignaciones->first(); @endphp
            <tr>
                <td>@include('reports.activos.partials.sku', ['codigo' => $activo->codigo_inventario])</td>
                <td><strong>{{ $activo->producto?->nombre ?? '—' }}</strong></td>
                <td style="text-align: right;">
                    @include('reports.activos.partials.costo', ['monto' => $activo->costo_adquisicion, 'monedaSimbolo' => $activo->moneda?->simbolo])
                </td>
                <td style="text-align: center;">{{ $activo->moneda?->codigo ?? '—' }}</td>
                <td style="font-size:9px;">{{ $ultimaAsignacion?->destinoLabel() ?? 'Sin registro' }}</td>
                <td style="font-size:9px;color:#555;">
                    {{ $ultimaAsignacion?->fecha_inicio?->format('d/m/Y') ?? '—' }}
                    @if($ultimaAsignacion?->fecha_fin)
                        — {{ $ultimaAsignacion->fecha_fin->format('d/m/Y') }}
                    @endif
                </td>
            </tr>
        @empty
            @include('reports.activos.partials.empty-state', ['colspan' => 6, 'mensaje' => 'No se registran activos extraviados.'])
        @endforelse
    </tbody>
    @if(($esUltimaPagina ?? false) && count($items) > 0)
        @include('reports.activos.partials.table-total', [
            'labelColspan' => 2,
            'label' => 'Total Costo Extraviados:',
            'total' => $totalCosto ?? 0,
            'monedaSimbolo' => 'C$',
            'count' => ($totalRegistros ?? count($items)) . ' extraviados',
        ])
    @endif
</table>
