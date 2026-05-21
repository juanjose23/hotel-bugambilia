@extends('reports.inventario.base')

@section('report_code', 'HTB-INV-001')
@section('report_name', 'Stock Actual por Producto')

@php
    $titulo = 'Stock Actual por Producto';
    $codigo = 'HTB-INV-001';
    $kpis = [
        ['label' => 'Productos en Stock', 'valor' => $filas->count()],
        ['label' => 'Stock Total Disp.', 'valor' => number_format($filas->sum('stock_disponible'), 2)],
        ['label' => 'En Cuarentena', 'valor' => number_format($filas->sum('stock_cuarentena'), 2)],
    ];
@endphp

@section('tabla')
<table class="data-table">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Variante</th>
            <th>Categoría</th>
            <th>Ubicación</th>
            <th style="text-align:right;">Disponible</th>
            <th style="text-align:right;">Cuarentena</th>
            <th style="text-align:center;">Lotes</th>
        </tr>
    </thead>
    <tbody>
        @forelse($filas as $row)
        <tr>
            <td><strong>{{ $row->producto }}</strong></td>
            <td>{{ $row->variante ?? '—' }}</td>
            <td>{{ $row->categoria ?? '—' }}</td>
            <td>{{ $row->ubicacion }}</td>
            <td style="text-align:right;color:#16a34a;font-weight:bold;">{{ number_format((float)$row->stock_disponible, 2) }}</td>
            <td style="text-align:right;{{ $row->stock_cuarentena > 0 ? 'color:#d97706;font-weight:bold;' : 'color:#9ca3af;' }}">
                {{ number_format((float)$row->stock_cuarentena, 2) }}
            </td>
            <td style="text-align:center;">{{ $row->total_lotes }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:#9ca3af;">Sin datos de stock disponible.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
