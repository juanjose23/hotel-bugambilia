@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Devoluciones a Proveedores',
    'codigo' => $codigoReporte ?? 'HTB-COM-016',
    'fechaInicio' => $fechaInicio ?? null,
    'fechaFin' => $fechaFin ?? null,
])

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio ?? null,
        'fechaFin' => $fechaFin ?? null,
        'tableView' => 'reports.compras.devoluciones.tables.devoluciones-proveedor',
    ])
@endsection
