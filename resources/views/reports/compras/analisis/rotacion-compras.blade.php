@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])
@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio ?? 'Histórico',
        'fechaFin' => $fechaFin ?? 'Hoy',
        'tableView' => 'reports.compras.analisis.tables.rotacion',
    ])
@endsection

