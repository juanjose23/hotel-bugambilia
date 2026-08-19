@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])
@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'tableData' => [
            'totalValorResidual' => $totalValorResidual ?? 0,
            'totalRegistros' => $totalRegistros,
        ],
        'tableView' => 'reports.activos.tables.dados-de-baja',
    ])
@endsection
