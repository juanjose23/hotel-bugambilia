@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => now()->subMonths($meses)->toDateString(),
        'fechaFin' => now()->toDateString(),
        'extraFilters' => [
            'Período de Análisis' => $meses . ' meses',
        ],
        'tableView' => 'reports.inventario.stock.tables.rotacion',
    ])
@endsection
