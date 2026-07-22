@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])
@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'totalRegistros' => $totalRegistros,
        'tableView' => 'reports.activos.tables.mantenimientos-vencidos',
    ])
@endsection
