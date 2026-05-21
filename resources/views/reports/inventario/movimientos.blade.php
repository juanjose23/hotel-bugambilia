@extends('reports.inventario.base')
@php
    $titulo = 'Movimientos de Inventario';
    $codigo = 'HTB-INV-003';
    $entradas = $movimientos->filter(fn($m) => in_array($m->tipo, ['ENTRADA_COMPRA', 'AJUSTE_ENTRADA', 'TRASLADO_ENTRADA']))->sum('cantidad');
    $salidas = $movimientos->filter(fn($m) => in_array($m->tipo, ['SALIDA_VENTA', 'SALIDA_PRODUCCION', 'AJUSTE_SALIDA', 'AJUSTE_CADUCIDAD', 'BAJA_CALIDAD', 'DEVOLUCION_PROVEEDOR']))->sum('cantidad');
    $kpis = [
        ['label' => 'Total Movimientos', 'valor' => $movimientos->count()],
        ['label' => 'Volumen Entradas', 'valor' => number_format($entradas, 2)],
        ['label' => 'Volumen Salidas', 'valor' => number_format($salidas, 2)],
    ];
    $desde = $filtros['fecha_desde'] ?? '—';
    $hasta = $filtros['fecha_hasta'] ?? '—';
    $tipoFiltro = !empty($filtros['tipo']) ? " | Tipo: {$filtros['tipo']}" : '';
    $filtrosTexto = "Período: {$desde} a {$hasta}{$tipoFiltro}";
@endphp
@section('tabla')
<table class="data-table">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Producto</th>
            <th>Lote</th>
            <th style="text-align:right;">Cantidad</th>
            <th>Origen</th>
            <th>Destino</th>
        </tr>
    </thead>
    <tbody>
        @forelse($movimientos as $m)
        <tr>
            <td style="font-size:9px;">{{ $m->created_at?->format('d/m/Y H:i') }}</td>
            <td style="font-size:9px;"><strong>{{ $m->tipo }}</strong></td>
            <td>{{ $m->producto?->nombre }}</td>
            <td style="font-family:monospace;font-size:9px;">{{ $m->lote?->codigo_lote }}</td>
            <td style="text-align:right;font-weight:bold;">{{ number_format((float)$m->cantidad, 2) }}</td>
            <td style="font-size:9px;">{{ $m->ubicacionOrigen?->nombre ?? '—' }}</td>
            <td style="font-size:9px;">{{ $m->ubicacionDestino?->nombre ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:#9ca3af;">Sin movimientos en el período indicado.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
