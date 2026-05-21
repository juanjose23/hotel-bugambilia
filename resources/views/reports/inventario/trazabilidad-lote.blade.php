@extends('reports.inventario.base')
@php
    $titulo = 'Trazabilidad de Lote';
    $codigo = 'HTB-INV-011';
    $kpis = [
        ['label' => 'Lote Interno', 'valor' => $lote->codigo_lote],
        ['label' => 'Producto', 'valor' => $lote->producto?->nombre],
        ['label' => 'Lote Proveedor', 'valor' => $lote->lote_proveedor ?? '—'],
    ];
@endphp
@section('tabla')

<div style="margin-bottom:20px;padding:10px;background:#f8fafc;border:1px solid #e2e8f0;">
    <table style="width:100%;font-size:10px;">
        <tr>
            <td style="width:50%;">
                <strong>Recepción Original:</strong> {{ $lote->recepcionItem?->recepcion?->codigo ?? '—' }}<br>
                <strong>Fecha Recepción:</strong> {{ $lote->fecha_recepcion?->format('d/m/Y') ?? '—' }}<br>
                <strong>Proveedor:</strong> {{ $lote->recepcionItem?->recepcion?->ordenCompra?->proveedor?->persona?->nombre_completo ?? '—' }}
            </td>
            <td style="width:50%;text-align:right;">
                <strong>Estado Actual:</strong> {{ $lote->estado?->label() }}<br>
                <strong>Cantidad Inicial:</strong> {{ number_format((float)$lote->cantidad_inicial, 2) }}<br>
                <strong>Cantidad Disponible:</strong> {{ number_format((float)$lote->cantidad_disponible, 2) }}
            </td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width:100px;">Fecha</th>
            <th style="width:150px;">Evento</th>
            <th style="text-align:right;">Cantidad</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Referencia</th>
        </tr>
    </thead>
    <tbody>
        {{-- Evento inicial simulado --}}
        <tr>
            <td style="font-size:9px;">{{ $lote->fecha_recepcion?->format('d/m/Y') ?? $lote->created_at?->format('d/m/Y') }}</td>
            <td style="font-size:9px;"><strong>ENTRADA INICIAL</strong></td>
            <td style="text-align:right;font-weight:bold;color:#16a34a;">+{{ number_format((float)$lote->cantidad_inicial, 2) }}</td>
            <td style="font-size:9px;">Proveedor</td>
            <td style="font-size:9px;">{{ $lote->ubicacion?->nombre ?? '—' }}</td>
            <td style="font-size:9px;">{{ $lote->recepcionItem?->recepcion?->codigo ?? '—' }}</td>
        </tr>
        @forelse($movimientos as $m)
        <tr>
            <td style="font-size:9px;">{{ $m->created_at?->format('d/m/Y H:i') }}</td>
            <td style="font-size:9px;"><strong>{{ $m->tipo }}</strong></td>
            <td style="text-align:right;font-weight:bold;{{ in_array($m->tipo, ['SALIDA_VENTA', 'SALIDA_PRODUCCION', 'AJUSTE_CADUCIDAD', 'BAJA_CALIDAD', 'DEVOLUCION_PROVEEDOR']) ? 'color:#dc2626;' : '' }}">
                {{ number_format((float)$m->cantidad, 2) }}
            </td>
            <td style="font-size:9px;">{{ $m->ubicacionOrigen?->nombre ?? '—' }}</td>
            <td style="font-size:9px;">{{ $m->ubicacionDestino?->nombre ?? '—' }}</td>
            <td style="font-size:9px;">{{ $m->referencia }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:#9ca3af;">Sin movimientos posteriores registrados.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
