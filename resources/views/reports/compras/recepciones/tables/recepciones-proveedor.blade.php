<table class="data-table">
    <thead><tr>
        <th>Proveedor</th>
        <th style="text-align:center;">N° Recepciones</th>
        <th style="text-align:right;">Total Cant. Recibida</th>
        <th style="text-align:right;">Total Cant. Rechazada</th>
        <th style="text-align:right;">Monto Total Recibido</th>
        <th style="text-align:center;">% Rechazo</th>
    </tr></thead>
    <tbody>
        @php $totalMonto = 0; @endphp
        @forelse($items as $row)
        @php
            $totalMonto += (float)$row->monto_total_recibido;
            $totalRecibido = (float)$row->total_cantidad_recibida;
            $totalRechazado = (float)$row->total_cantidad_rechazada;
            $pctRechazo = $totalRecibido + $totalRechazado > 0 ? round($totalRechazado / ($totalRecibido + $totalRechazado) * 100, 1) : 0;
        @endphp
        <tr>
            <td><strong>{{ $row->proveedor_nombre }}</strong></td>
            <td style="text-align:center;">{{ $row->total_recepciones }}</td>
            <td style="text-align:right;color:#16a34a;font-weight:bold;">{{ number_format($totalRecibido, 2) }}</td>
            <td style="text-align:right;color:{{ $totalRechazado > 0 ? '#dc2626' : '#555' }};font-weight:bold;">{{ number_format($totalRechazado, 2) }}</td>
            <td style="text-align:right;font-weight:bold;color:#711C37;">${{ number_format((float)$row->monto_total_recibido, 2) }}</td>
            <td style="text-align:center;color:{{ $pctRechazo > 5 ? '#dc2626' : '#555' }};font-weight:bold;">{{ $pctRechazo }}%</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:#888;padding:20px;">Sin recepciones en el período.</td></tr>
        @endforelse
    </tbody>
    @if(count($items) > 0)
    <tfoot><tr style="background:#f1f5f9;">
        <td colspan="4" style="text-align:right;font-weight:bold;text-transform:uppercase;padding:8px;">Total General:</td>
        <td style="text-align:right;font-weight:bold;color:#711C37;font-size:13px;padding:8px;">${{ number_format($totalMonto, 2) }}</td>
        <td></td>
    </tr></tfoot>
    @endif
</table>
