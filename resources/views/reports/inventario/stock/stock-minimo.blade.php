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
        'tableView' => 'reports.inventario.stock.tables.stock-minimo',
    ])
@endsection
