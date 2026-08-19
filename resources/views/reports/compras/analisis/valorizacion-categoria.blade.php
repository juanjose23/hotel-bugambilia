@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Valorización de Compras por Categórica',
    'codigo' => $codigoReporte ?? 'HTB-COM-013',
    'fechaInicio' => $fechaInicio ?? null,
    'fechaFin' => $fechaFin ?? null,
])

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio ?? null,
        'fechaFin' => $fechaFin ?? null,
        'tableData' => [
            'totalGeneral' => $totalGeneral ?? 0,
        ],
        'tableView' => 'reports.compras.analisis.tables.valorizacion-categoria',
    ])
@endsection
