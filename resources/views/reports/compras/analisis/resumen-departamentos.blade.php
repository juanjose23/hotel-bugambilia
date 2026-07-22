@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])
@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio,
        'fechaFin' => $fechaFin,
        'tableView' => 'reports.compras.analisis.tables.resumen-departamentos',
    ])
@endsection

