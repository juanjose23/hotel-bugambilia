<table>
    <thead>
        <tr>
            <th colspan="7">Hotel Bugambilias — HTB-INV-001: Stock Actual por Producto</th>
        </tr>
        <tr>
            <th colspan="7">Generado: {{ $fecha }}</th>
        </tr>
        <tr></tr>
        <tr>
            <th>Producto</th>
            <th>Variante</th>
            <th>Categoría</th>
            <th>Ubicación</th>
            <th>Stock Disponible</th>
            <th>Stock Cuarentena</th>
            <th>Total Lotes</th>
        </tr>
    </thead>
    <tbody>
        @foreach($filas as $row)
        <tr>
            <td>{{ $row->producto }}</td>
            <td>{{ $row->variante ?? '' }}</td>
            <td>{{ $row->categoria ?? '' }}</td>
            <td>{{ $row->ubicacion }}</td>
            <td>{{ $row->stock_disponible }}</td>
            <td>{{ $row->stock_cuarentena }}</td>
            <td>{{ $row->total_lotes }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4"><strong>TOTALES</strong></td>
            <td><strong>{{ $filas->sum('stock_disponible') }}</strong></td>
            <td><strong>{{ $filas->sum('stock_cuarentena') }}</strong></td>
            <td><strong>{{ $filas->sum('total_lotes') }}</strong></td>
        </tr>
    </tbody>
</table>
