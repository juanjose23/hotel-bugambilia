@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])
@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'totalRegistros' => $totalRegistros,
        'alertMessage' => 'Total costo de activos extraviados: $' . number_format((float) ($totalCosto ?? 0), 2),
        'tableView' => 'reports.activos.tables.extraviados',
    ])
@endsection
