<table>
    <thead>
        <tr><th colspan="6">Hotel Bugambilias — HTB-INV-006: Mermas / Lotes Dados de Baja</th></tr>
        <tr><th colspan="6">Generado: {{ $fecha }}</th></tr>
        <tr></tr>
        <tr><th>Código Lote</th><th>Producto</th><th>Estado</th><th>Cantidad</th><th>Fecha Vencimiento</th><th>Ubicación</th></tr>
    </thead>
    <tbody>
        @foreach($lotes as $lote)
        <tr>
            <td>{{ $lote->codigo_lote }}</td>
            <td>{{ $lote->producto?->nombre }}</td>
            <td>{{ $lote->estado?->label() }}</td>
            <td>{{ $lote->cantidad_inicial }}</td>
            <td>{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
            <td>{{ $lote->ubicacion?->nombre }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
