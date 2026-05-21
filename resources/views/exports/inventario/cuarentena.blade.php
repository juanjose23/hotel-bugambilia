<table>
    <thead>
        <tr><th colspan="6">Hotel Bugambilias — HTB-INV-004: Lotes en Cuarentena</th></tr>
        <tr><th colspan="6">Generado: {{ $fecha }}</th></tr>
        <tr></tr>
        <tr><th>Código Lote</th><th>Producto</th><th>Cantidad</th><th>Ubicación</th><th>Días en Cuarentena</th><th>Fecha Vence</th></tr>
    </thead>
    <tbody>
        @foreach($lotes as $lote)
        <tr>
            <td>{{ $lote->codigo_lote }}</td>
            <td>{{ $lote->producto?->nombre }}</td>
            <td>{{ $lote->cantidad_disponible }}</td>
            <td>{{ $lote->ubicacion?->nombre }}</td>
            <td>{{ now()->diffInDays($lote->updated_at) }}</td>
            <td>{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
