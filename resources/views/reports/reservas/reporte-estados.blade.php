@extends('reports.layout.app', [
    'titulo' => $titulo ?? 'Reservas Agrupadas por Estado',
    'codigo' => $codigo ?? 'HTB-RES-003',
    'fechaInicio' => $fechaInicio ?? null,
    'fechaFin' => $fechaFin ?? null,
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
    .empty-row { text-align: center; color: #64748b; padding: 14px; }
@endsection

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio ?? null,
        'fechaFin' => $fechaFin ?? null,
        'tableView' => 'reports.reservas.tables.reporte-estados',
    ])
@endsection
