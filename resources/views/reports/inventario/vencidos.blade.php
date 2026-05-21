@extends('reports.inventario.base')
@php
    $titulo = "Lotes Vencidos (Expirados)";
    $codigo = 'HTB-INV-012';
    $kpis = [
        ['label' => 'Total Lotes Vencidos', 'valor' => $lotes->count()],
        ['label' => 'Stock Vencido Total', 'valor' => number_format($lotes->sum('cantidad_disponible'), 2)],
    ];
@endphp
@section('tabla')
<table class="data-table">
    <thead>
        <tr>
            <th>Código Lote</th><th>Producto</th><th style="text-align:right;">Disponible</th>
            <th>Ubicación</th><th style="text-align:center;">Vence</th><th style="text-align:center;">Días Transcurridos</th>
        </tr>
    </thead>
    <tbody>
        @forelse($lotes as $lote)
        @php $diasVencido = now()->diffInDays($lote->fecha_vencimiento); @endphp
        <tr>
            <td style="font-family:monospace;font-size:9px;">{{ $lote->codigo_lote }}</td>
            <td>{{ $lote->producto?->nombre }}</td>
            <td style="text-align:right;">{{ number_format((float)$lote->cantidad_disponible, 2) }}</td>
            <td>{{ $lote->ubicacion?->nombre }}</td>
            <td style="text-align:center;">{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
            <td style="text-align:center;color:#dc2626;font-weight:bold;">{{ $diasVencido }} días vencido</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:#9ca3af;">No se encontraron lotes vencidos en el inventario.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
