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
        'tableView' => 'reports.compras.recepciones.tables.recepciones-proveedor',
        'alertMessage' => 'Un porcentaje de rechazo mayor al 5% puede indicar problemas de calidad con ese proveedor.',
    ])
@endsection

