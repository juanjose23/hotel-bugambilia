@extends('reports.inventario.base')
@php
    $titulo = 'Lotes en Cuarentena';
    $codigo = 'HTB-INV-004';
    $kpis = [
        ['label' => 'Lotes en Cuarentena', 'valor' => $lotes->count()],
        ['label' => 'Unidades Retenidas', 'valor' => number_format($lotes->sum('cantidad_disponible'), 2)],
        ['label' => '> 7 días retenidos', 'valor' => $lotes->filter(fn($l) => now()->diffInDays($l->updated_at) > 7)->count()],
    ];
@endphp
@section('tabla')
<table class="data-table">
    <thead>
        <tr>
            <th>Código Lote</th><th>Producto</th><th style="text-align:right;">Cantidad</th>
            <th>Ubicación</th><th style="text-align:center;">Días en Cuarentena</th><th>Fecha Vence</th>
        </tr>
    </thead>
    <tbody>
        @forelse($lotes as $lote)
        @php $dias = now()->diffInDays($lote->updated_at); @endphp
        <tr>
            <td style="font-family:monospace;font-size:9px;">{{ $lote->codigo_lote }}</td>
            <td>{{ $lote->producto?->nombre }}</td>
            <td style="text-align:right;">{{ number_format((float)$lote->cantidad_disponible, 2) }}</td>
            <td>{{ $lote->ubicacion?->nombre }}</td>
            <td style="text-align:center;{{ $dias > 7 ? 'color:#dc2626;font-weight:bold;' : 'color:#d97706;' }}">{{ $dias }} días</td>
            <td>{{ $lote->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:#9ca3af;">Sin lotes en cuarentena.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
