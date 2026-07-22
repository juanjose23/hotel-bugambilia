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
            'Últimos Meses' => $meses,
        ],
        'tableView' => 'reports.compras.analisis.tables.analisis-precio',
    ])
@endsection

