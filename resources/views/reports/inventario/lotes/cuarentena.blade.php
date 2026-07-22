@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => now()->startOfMonth()->toDateString(),
        'fechaFin' => now()->toDateString(),
        'tableView' => 'reports.inventario.lotes.tables.cuarentena',
    ])
@endsection
