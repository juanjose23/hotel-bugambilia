@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => now()->toDateString(),
        'fechaFin' => now()->toDateString(),
        'extraFilters' => [
            'Valor Total General' => ($simboloMoneda ?? 'C$') . ' ' . number_format($totalGeneral, 2),
        ],
        'tableView' => 'reports.inventario.stock.tables.valorizacion',
    ])
@endsection
