@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Ranking de Proveedores',
    'codigo' => $codigoReporte ?? 'HTB-COM-015',
    'fechaInicio' => $fechaInicio ?? null,
    'fechaFin' => $fechaFin ?? null,
])

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio ?? null,
        'fechaFin' => $fechaFin ?? null,
        'tableView' => 'reports.compras.proveedores.tables.ranking-proveedores',
    ])
@endsection
