<table class="data-table">
    <thead>
        <tr>
            <th>Categoría</th>
            <th style="text-align:center;">N° Órdenes</th>
            <th style="text-align:right;">Total Invertido</th>
            <th style="text-align:center;">% del Gasto</th>
            <th style="width:30%;">Distribución</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $row)
            <tr>
                <td><strong>{{ $row->categoria }}</strong></td>
                <td style="text-align:center;">{{ $row->total_ordenes }}</td>
                <td style="text-align:right; font-weight:bold; color:#711C37;">${{ number_format((float)$row->total_invertido, 2) }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $row->porcentaje }}%</td>
                <td>
                    <div style="width:100%; background:#e2e8f0; height:8px; border-radius:4px; overflow:hidden;">
                        <div style="width:{{ min((float)$row->porcentaje, 100) }}%; background:#711C37; height:100%;"></div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center; color:#64748b; padding:16px;">Sin datos en el período.</td>
            </tr>
        @endforelse
    </tbody>
    @if(($esUltimaPagina ?? false) && count($items) > 0)
        <tfoot>
            <tr style="background:#f8fafc;">
                <td colspan="2" style="text-align:right; font-weight:bold; text-transform:uppercase; padding:8px;">Total General:</td>
                <td style="text-align:right; font-weight:bold; color:#711C37; font-size:11pt; padding:8px;">${{ number_format((float)($totalGeneral ?? 0), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    @endif
</table>
