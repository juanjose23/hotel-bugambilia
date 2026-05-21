@extends('reports.inventario.base')
@php
    $titulo = 'Valorización del Inventario';
    $codigo = 'HTB-INV-007';
    $kpis = [
        ['label' => 'Total Valorizado', 'valor' => '$' . number_format($totalGeneral, 2)],
        ['label' => 'Categorías Diferentes', 'valor' => $filas->unique('categoria')->count()],
        ['label' => 'Productos Valorizados', 'valor' => $filas->count()],
    ];
@endphp
@section('tabla')
<table class="data-table">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Categoría</th>
            <th>Ubicación</th>
            <th style="text-align:right;">Stock Total</th>
            <th style="text-align:right;">Costo Prom.</th>
            <th style="text-align:right;">Valor Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($filas as $row)
        <tr>
            <td><strong>{{ $row->producto }}</strong></td>
            <td>{{ $row->categoria ?? '—' }}</td>
            <td>{{ $row->ubicacion }}</td>
            <td style="text-align:right;">{{ number_format((float)$row->stock_total, 2) }}</td>
            <td style="text-align:right;">${{ number_format((float)$row->costo_promedio, 4) }}</td>
            <td style="text-align:right;color:#16a34a;font-weight:bold;">${{ number_format((float)$row->valor_total, 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:#9ca3af;">Sin datos de valorización.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align:right;font-weight:bold;text-transform:uppercase;padding:10px;">Total General Valorizado:</td>
            <td style="text-align:right;font-weight:bold;color:#16a34a;font-size:14px;padding:10px;">${{ number_format($totalGeneral, 2) }}</td>
        </tr>
    </tfoot>
</table>
@endsection
