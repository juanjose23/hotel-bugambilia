@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio ?? null,
        'fechaFin' => $fechaFin ?? null,
        'tableData' => [
            'totalPerdida' => $totalPerdida ?? 0,
            'totalRegistros' => $totalRegistros,
        ],
        'tableView' => 'reports.inventario.mermas.tables.mermas',
    ])
@endsection
