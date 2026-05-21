<table>
    <thead>
        <tr><th colspan="5">Hotel Bugambilias — HTB-INV-009: Mermas Totales (Pérdidas)</th></tr>
        <tr><th colspan="5">Generado: {{ $fecha }}</th></tr>
        <tr></tr>
        <tr><th>Categoría</th><th>Producto</th><th>Cantidad Perdida</th><th>Costo Unitario</th><th>Pérdida Total</th></tr>
    </thead>
    <tbody>
        @foreach($filas as $row)
        <tr>
            <td>{{ $row->categoria }}</td>
            <td>{{ $row->producto }}</td>
            <td>{{ number_format((float)$row->cantidad_perdida, 2) }}</td>
            <td>{{ number_format((float)$row->costo_unitario, 4) }}</td>
            <td>{{ number_format((float)$row->perdida_total, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4"><strong>TOTAL PÉRDIDAS</strong></td>
            <td><strong>{{ number_format((float)$totalPerdidas, 2) }}</strong></td>
        </tr>
    </tbody>
</table>
