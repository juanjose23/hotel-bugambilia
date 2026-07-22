<table class="data-table">
    <thead>
        <tr>
            <th>Código Inventario</th>
            <th>Producto</th>
            <th style="text-align: center;">Tipo de Baja</th>
            <th>Motivo / Detalle</th>
            <th style="text-align: center;">Fecha de Baja</th>
            <th style="text-align: right;">Valor Residual</th>
            <th>Creado Por</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $baja)
            <tr>
                <td>@include('reports.activos.partials.sku', ['codigo' => $baja->activo?->codigo_inventario ?? '—'])</td>
                <td><strong>{{ $baja->activo?->producto?->nombre ?? '—' }}</strong></td>
                <td style="text-align: center;">
                    <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #991b1b;">
                        {{ $baja->motivo_tipo?->label() ?? '—' }}
                    </span>
                </td>
                <td style="font-size:9px;color:#555;">{{ $baja->motivo_detalle ?? '—' }}</td>
                <td style="text-align: center;">{{ $baja->fecha_baja?->format('d/m/Y') ?? '—' }}</td>
                <td style="text-align: right;">
                    @include('reports.activos.partials.costo', ['monto' => $baja->valor_residual ?? 0, 'monedaSimbolo' => '$'])
                </td>
                <td style="font-size:9px;">{{ $baja->creadoPor?->name ?? '—' }}</td>
            </tr>
        @empty
            @include('reports.activos.partials.empty-state', ['colspan' => 7, 'mensaje' => 'No se registran activos dados de baja.'])
        @endforelse
    </tbody>
</table>
