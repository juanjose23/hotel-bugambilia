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
        'extraFilters' => !empty($filtros['tipo']) ? ['Tipo' => $filtros['tipo']] : [],
        'tableView' => 'reports.inventario.movimientos.tables.movimientos',
    ])
@endsection
