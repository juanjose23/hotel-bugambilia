<table>
    <thead>
        <tr><th colspan="5">Hotel Bugambilias — HTB-INV-008: Rotación de Inventario ({{ $meses }} meses)</th></tr>
        <tr><th colspan="5">Generado: {{ $fecha }}</th></tr>
        <tr></tr>
        <tr><th>Producto</th><th>Stock Promedio</th><th>Total Salidas</th><th>Índice de Rotación</th><th>Clasificación</th></tr>
    </thead>
    <tbody>
        @foreach($filas as $row)
        <tr>
            <td>{{ $row->producto }}</td>
            <td>{{ number_format((float)$row->stock_promedio, 2) }}</td>
            <td>{{ number_format((float)$row->total_salidas, 2) }}</td>
            <td>{{ number_format((float)$row->indice_rotacion, 2) }}</td>
            <td>{{ $row->clasificacion }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
