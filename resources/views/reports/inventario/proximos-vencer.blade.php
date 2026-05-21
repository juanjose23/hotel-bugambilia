@extends('reports.inventario.base')
@php
    $titulo = "Lotes Próximos a Vencer ({$dias} días)";
    $codigo = 'HTB-INV-005';
    $kpis = [
        ['label' => 'Lotes por Vencer', 'valor' => $lotes->count()],
        ['label' => 'Stock en Riesgo', 'valor' => number_format($lotes->sum('cantidad_disponible'), 2)],
        ['label' => '< 7 días', 'valor' => $lotes->filter(fn($l) => now()->diffInDays($l->fecha_vencimiento) < 7)->count()],
    ];
@endphp
@section('tabla')
<table class="data-table">
    <thead>
        <tr>
            <th>Código Lote</th><th>Producto</th><th style="text-align:right;">Disponible</th>
            <th>Ubicación</th><th style="text-align:center;">Vence</th><th style="text-align:center;">Días Restantes</th>
        </tr>
    </thead>
    <tbody>
        @forelse($lotes as $lote)
        @php $diasRestantes = now()->diffInDays($lote->fecha_vencimiento); @endphp
        <tr>
            <td style="font-family:monospace;font-size:9px;">{{ $lote->codigo_lote }}</td>
            <td>{{ $lote->producto?->nombre }}</td>
            <td style="text-align:right;">{{ number_format((float)$lote->cantidad_disponible, 2) }}</td>
            <td>{{ $lote->ubicacion?->nombre }}</td>
            <td style="text-align:center;">{{ $lote->fecha_vencimiento?->format('d/m/Y') }}</td>
            <td style="text-align:center;{{ $diasRestantes < 7 ? 'color:#dc2626;font-weight:bold;' : 'color:#d97706;' }}">{{ $diasRestantes }} días</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:#9ca3af;">Sin lotes próximos a vencer en el período indicado.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
