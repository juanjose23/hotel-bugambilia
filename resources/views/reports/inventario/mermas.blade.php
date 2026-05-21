@extends('reports.inventario.base')
@php
    $titulo = 'Mermas / Lotes Dados de Baja';
    $codigo = 'HTB-INV-006';
    $kpis = [
        ['label' => 'Lotes con Merma', 'valor' => $lotes->count()],
        ['label' => 'Vencidos', 'valor' => $lotes->filter(fn($l) => $l->estado?->value === 3)->count()],
        ['label' => 'Rechazados', 'valor' => $lotes->filter(fn($l) => $l->estado?->value === 4)->count()],
    ];
    $desde = $filtros['periodo_desde'] ?? '—';
    $hasta = $filtros['periodo_hasta'] ?? '—';
    $filtrosTexto = "Período: {$desde} a {$hasta}" . (!empty($filtros['motivo']) ? " | Motivo: {$filtros['motivo']}" : '');
@endphp
@section('tabla')
<table class="data-table">
    <thead>
        <tr>
            <th>Código Lote</th><th>Producto</th><th>Estado</th>
            <th style="text-align:right;">Cantidad Inicial</th><th>Fecha Vencimiento</th><th>Ubicación</th>
        </tr>
    </thead>
    <tbody>
        @forelse($lotes as $lote)
        <tr>
            <td style="font-family:monospace;font-size:9px;">{{ $lote->codigo_lote }}</td>
            <td>{{ $lote->producto?->nombre }}</td>
            <td style="color:#dc2626;font-weight:bold;">{{ $lote->estado?->label() }}</td>
            <td style="text-align:right;">{{ number_format((float)$lote->cantidad_inicial, 2) }}</td>
            <td>{{ $lote->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $lote->ubicacion?->nombre }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:#9ca3af;">Sin mermas en el período indicado.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
