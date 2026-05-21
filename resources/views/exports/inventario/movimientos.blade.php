<table>
    <thead>
        <tr><th colspan="8">Hotel Bugambilias — HTB-INV-003: Movimientos de Inventario</th></tr>
        <tr><th colspan="8">Generado: {{ $fecha }}</th></tr>
        <tr></tr>
        <tr>
            <th>Fecha</th><th>Tipo</th><th>Producto</th><th>Lote</th>
            <th>Cantidad</th><th>Origen</th><th>Destino</th><th>Referencia</th>
        </tr>
    </thead>
    <tbody>
        @foreach($movimientos as $m)
        <tr>
            <td>{{ $m->created_at?->format('d/m/Y H:i') }}</td>
            <td>{{ $m->tipo }}</td>
            <td>{{ $m->producto?->nombre }}</td>
            <td>{{ $m->lote?->codigo_lote }}</td>
            <td>{{ $m->cantidad }}</td>
            <td>{{ $m->ubicacionOrigen?->nombre }}</td>
            <td>{{ $m->ubicacionDestino?->nombre ?? 'Salida del sistema' }}</td>
            <td>{{ $m->referencia }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
