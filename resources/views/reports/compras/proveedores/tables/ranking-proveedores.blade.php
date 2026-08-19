<table class="data-table">
    <thead>
        <tr>
            <th style="text-align:center;">#</th>
            <th>Proveedor</th>
            <th style="text-align:center;">OCs Emitidas</th>
            <th style="text-align:center;">OCs Recibidas</th>
            <th style="text-align:center;">Lead Time Prom.</th>
            <th style="text-align:center;">Devoluciones</th>
            <th style="text-align:center;">% Dev.</th>
            <th style="text-align:right;">Monto Comprado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $row)
            @php
                $leadColor = $row->promedio_dias_entrega <= 7 ? '#047857' : ($row->promedio_dias_entrega <= 14 ? '#b45309' : '#b91c1c');
                $devColor = $row->porcentaje_devoluciones > 10 ? '#b91c1c' : ($row->porcentaje_devoluciones > 5 ? '#b45309' : '#047857');
            @endphp
            <tr>
                <td style="text-align:center; font-weight:bold; color:#711C37;">{{ ($paginaIndex ?? 0) * 15 + $loop->iteration }}</td>
                <td><strong>{{ $row->proveedor_nombre }}</strong></td>
                <td style="text-align:center;">{{ $row->total_ordenes }}</td>
                <td style="text-align:center;">{{ $row->ordenes_recibidas }}</td>
                <td style="text-align:center; font-weight:bold; color:{{ $leadColor }};">{{ $row->promedio_dias_entrega }} días</td>
                <td style="text-align:center;">{{ $row->total_devoluciones }}</td>
                <td style="text-align:center; font-weight:bold; color:{{ $devColor }};">{{ $row->porcentaje_devoluciones }}%</td>
                <td style="text-align:right; font-weight:bold; color:#711C37;">${{ number_format((float)$row->monto_total, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#64748b; padding:16px;">Sin proveedores con órdenes en el período.</td>
            </tr>
        @endforelse
    </tbody>
</table>
