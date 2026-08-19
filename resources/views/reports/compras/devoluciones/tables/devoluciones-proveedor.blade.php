<table class="data-table">
    <thead>
        <tr>
            <th>Código Dev.</th>
            <th>Proveedor</th>
            <th>OC Relacionada</th>
            <th style="text-align:center;">Fecha</th>
            <th>Estado</th>
            <th>Motivo</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $row)
            <tr>
                <td><span class="sku-code">{{ $row->codigo ?? '-' }}</span></td>
                <td>{{ $row->proveedor_nombre ?? '-' }}</td>
                <td style="font-size:8pt;">{{ $row->orden_codigo ?? '-' }}</td>
                <td style="text-align:center;">{{ isset($row->fecha_devolucion) ? \Carbon\Carbon::parse($row->fecha_devolucion)->format('d/m/Y') : '—' }}</td>
                <td><span class="badge badge-info">{{ $row->estado ?? 'Pendiente' }}</span></td>
                <td style="font-size:8pt; color:#64748b;">{{ $row->motivo ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center; color:#64748b; padding:16px;">Sin devoluciones registradas en el período.</td>
            </tr>
        @endforelse
    </tbody>
</table>
