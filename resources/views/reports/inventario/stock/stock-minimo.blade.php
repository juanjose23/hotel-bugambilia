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
        'tableData' => [
            'totalStockActual' => $totalStockActual ?? 0,
            'totalPendiente' => $totalPendiente ?? 0,
            'totalCriticos' => $totalCriticos ?? 0,
        ],
        'tableView' => 'reports.inventario.stock.tables.stock-minimo',
    ])
@endsection
