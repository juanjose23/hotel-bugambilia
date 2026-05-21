<table>
    <thead>
        <tr><th colspan="6">Hotel Bugambilias — HTB-INV-007: Valorización del Inventario</th></tr>
        <tr><th colspan="6">Generado: {{ $fecha }}</th></tr>
        <tr></tr>
        <tr><th>Producto</th><th>Categoría</th><th>Ubicación</th><th>Stock Total</th><th>Costo Promedio</th><th>Valor Total</th></tr>
    </thead>
    <tbody>
        @foreach($filas as $row)
        <tr>
            <td>{{ $row->producto }}</td>
            <td>{{ $row->categoria ?? '' }}</td>
            <td>{{ $row->ubicacion }}</td>
            <td>{{ $row->stock_total }}</td>
            <td>{{ number_format((float)$row->costo_promedio, 4) }}</td>
            <td>{{ number_format((float)$row->valor_total, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5"><strong>TOTAL GENERAL</strong></td>
            <td><strong>{{ number_format((float)$totalGeneral, 2) }}</strong></td>
        </tr>
    </tbody>
</table>
