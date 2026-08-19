@extends('reports.layout.app', [
    'titulo' => $titulo ?? 'Rendimiento por Categoría de Habitación',
    'codigo' => $codigo ?? 'HTB-RES-005',
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
    .empty-row { text-align: center; color: #64748b; padding: 14px; }
@endsection

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'tableView' => 'reports.reservas.tables.reporte-rendimiento-habitaciones',
    ])
@endsection
