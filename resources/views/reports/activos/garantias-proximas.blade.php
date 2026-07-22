@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])
@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'totalRegistros' => $totalRegistros,
        'extraFilters' => [
            'Días de anticipación' => ($dias ?? 90) . ' días',
        ],
        'tableView' => 'reports.activos.tables.garantias-proximas',
    ])
@endsection
