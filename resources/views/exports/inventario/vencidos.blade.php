<table>
    <thead>
        <tr><th colspan="6">Hotel Bugambilias — HTB-INV-012: Lotes Vencidos</th></tr>
        <tr><th colspan="6">Generado: {{ $fecha }}</th></tr>
        <tr></tr>
        <tr><th>Código Lote</th><th>Producto</th><th>Disponible</th><th>Ubicación</th><th>Fecha Vence</th><th>Días Vencido</th></tr>
    </thead>
    <tbody>
        @foreach($lotes as $lote)
        <tr>
            <td>{{ $lote->codigo_lote }}</td>
            <td>{{ $lote->producto?->nombre }}</td>
            <td>{{ $lote->cantidad_disponible }}</td>
            <td>{{ $lote->ubicacion?->nombre }}</td>
            <td>{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
            <td>{{ now()->diffInDays($lote->fecha_vencimiento) }} días</td>
        </tr>
        @endforeach
    </tbody>
</table>
