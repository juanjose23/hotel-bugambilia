@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])
@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio,
        'fechaFin' => $fechaFin,
        'extraFilters' => [
            'Estado Filtrado' => $estado,
        ],
        'tableView' => 'reports.compras.solicitudes.tables.solicitudes-estado',
    ])
@endsection

