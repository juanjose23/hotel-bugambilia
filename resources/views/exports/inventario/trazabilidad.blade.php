<table>
    <thead>
        <tr><th colspan="7">Hotel Bugambilias — HTB-INV-011: Trazabilidad de Lote {{ $lote->codigo_lote ?? '' }}</th></tr>
        <tr><th colspan="7">Generado: {{ $fecha }}</th></tr>
        <tr></tr>
        <tr><th>Fecha</th><th>Evento (Tipo)</th><th>Cantidad</th><th>Origen</th><th>Destino</th><th>Referencia</th><th>Notas</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $lote->fecha_recepcion?->format('d/m/Y') }}</td>
            <td>ENTRADA_COMPRA (Recepción inicial)</td>
            <td>{{ $lote->cantidad_inicial }}</td>
            <td>Proveedor</td>
            <td>{{ $lote->ubicacion?->nombre }}</td>
            <td>{{ $lote->recepcionItem?->recepcion?->codigo ?? '—' }}</td>
            <td>Lote proveedor: {{ $lote->lote_proveedor }}</td>
        </tr>
        @foreach($movimientos as $m)
        <tr>
            <td>{{ $m->created_at?->format('d/m/Y H:i') }}</td>
            <td>{{ $m->tipo }}</td>
            <td>{{ $m->cantidad }}</td>
            <td>{{ $m->ubicacionOrigen?->nombre ?? '—' }}</td>
            <td>{{ $m->ubicacionDestino?->nombre ?? '—' }}</td>
            <td>{{ $m->referencia }}</td>
            <td>{{ $m->notas }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
