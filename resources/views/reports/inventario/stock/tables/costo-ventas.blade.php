<table class="data-table">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Variante</th>
            <th>Categoría</th>
            <th style="text-align: right;">Cant. Comprada</th>
            <th style="text-align: right;">Costo Compras ({{ $simboloMoneda ?? 'C$' }})</th>
            <th style="text-align: right;">Cant. Consumida</th>
            <th style="text-align: right;">Costo Consumo ({{ $simboloMoneda ?? 'C$' }})</th>
            <th style="text-align: right;">Desviación (%)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $row)
            <tr>
                <td><strong>{{ $row->producto }}</strong></td>
                <td>{{ $row->variante ?? 'Sin Variante / Base' }}</td>
                <td>{{ $row->categoria ?? 'N/A' }}</td>
                <td style="text-align: right;">{{ number_format($row->cantidadComprada, 2) }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $simboloMoneda ?? 'C$' }} {{ number_format($row->costoCompras, 2) }}</td>
                <td style="text-align: right;">{{ number_format($row->cantidadConsumida, 2) }}</td>
                <td style="text-align: right; font-weight: bold; color: #711c37;">{{ $simboloMoneda ?? 'C$' }} {{ number_format($row->costoConsumo, 2) }}</td>
                <td style="text-align: right; font-weight: bold; color: {{ $row->desviacionPorcentaje > 0 ? '#b91c1c' : ($row->desviacionPorcentaje < 0 ? '#16a34a' : 'inherit') }};">
                    {{ $row->desviacionPorcentaje > 0 ? '+' : '' }}{{ number_format($row->desviacionPorcentaje, 2) }}%
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #718096; padding: 20px;">No se registraron compras ni consumos en este período.</td>
            </tr>
        @endforelse
    </tbody>
</table>
