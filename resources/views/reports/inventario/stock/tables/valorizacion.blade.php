<table class="data-table">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Categoría</th>
            <th>Ubicación / Bodega</th>
            <th style="text-align: right;">Stock Total</th>
            <th style="text-align: right;">Costo Promedio ({{ $monedaSimbolo ?? 'C$' }})</th>
            <th style="text-align: right;">Valor Total ({{ $monedaSimbolo ?? 'C$' }})</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $val)
            <tr>
                <td><strong>{{ $val->producto }}</strong></td>
                <td>{{ $val->categoria ?? 'N/A' }}</td>
                <td>{{ $val->ubicacion }}</td>
                <td style="text-align: right;">{{ number_format($val->stockTotal, 2) }}</td>
                <td style="text-align: right;">{{ number_format($val->costoPromedio, 2) }}</td>
                <td style="text-align: right; font-weight: bold; color: #15803d;">{{ number_format($val->valorTotal, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #718096; padding: 20px;">No hay existencias valoradas.</td>
            </tr>
        @endforelse
    </tbody>
    @if(($esUltimaPagina ?? false) && count($items) > 0)
        <tfoot>
            <tr style="background:#f1f5f9;">
                <td colspan="3" style="text-align:right; font-weight:bold; text-transform:uppercase; padding:10px;">Total General:</td>
                <td style="text-align:right; font-weight:bold; padding:10px;">{{ number_format($totalStock ?? 0, 2) }}</td>
                <td></td>
                <td style="text-align:right; font-weight:bold; color:#711C37; font-size:14px; padding:10px;">{{ $monedaSimbolo ?? 'C$' }} {{ number_format($valorTotal ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
