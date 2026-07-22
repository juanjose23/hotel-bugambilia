@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => now()->toDateString(),
        'fechaFin' => now()->addDays($dias)->toDateString(),
        'extraFilters' => [
            'Días de anticipación' => $dias,
        ],
        'tableView' => 'reports.inventario.lotes.tables.proximos-vencer',
    ])
@endsection
