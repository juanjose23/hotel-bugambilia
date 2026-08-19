@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('content')
    @php
        $lote  = $trazabilidad['lote']  ?? $trazabilidad->lote  ?? null;
        $items = $trazabilidad['movimientos'] ?? $trazabilidad->movimientos ?? collect();
    @endphp
    @include('reports.inventario.trazabilidad.tables.trazabilidad-lote', [
        'lote'  => $lote,
        'items' => $items,
    ])
@endsection
