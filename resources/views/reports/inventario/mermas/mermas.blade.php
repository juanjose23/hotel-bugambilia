@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $filtros['periodo_desde'] ?? null,
        'fechaFin' => $filtros['periodo_hasta'] ?? null,
        'tableView' => 'reports.inventario.mermas.tables.mermas',
    ])
@endsection
