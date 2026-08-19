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
            'totalStock' => $totalStock ?? 0,
            'valorTotal' => $valorTotal ?? 0,
            'monedaSimbolo' => $monedaSimbolo ?? 'C$',
        ],
        'tableView' => 'reports.inventario.stock.tables.valorizacion',
    ])
@endsection
