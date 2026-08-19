@extends('reports.layout.app', [
    'titulo' => $titulo ?? 'Listado y Fichas de Huéspedes',
    'codigo' => $codigo ?? 'HTB-RES-004',
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
    .empty-row { text-align: center; color: #64748b; padding: 14px; }
@endsection

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'tableView' => 'reports.reservas.tables.reporte-huespedes',
    ])
@endsection
