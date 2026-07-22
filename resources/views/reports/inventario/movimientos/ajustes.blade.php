@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $filtros['fecha_desde'] ?? null,
        'fechaFin' => $filtros['fecha_hasta'] ?? null,
        'tableView' => 'reports.inventario.movimientos.tables.ajustes',
    ])
@endsection
