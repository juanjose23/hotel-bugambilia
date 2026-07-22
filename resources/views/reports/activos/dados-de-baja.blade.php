@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])
@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'totalRegistros' => $totalRegistros,
        'alertMessage' => 'Total valor residual: $' . number_format((float) ($totalValorResidual ?? 0), 2),
        'tableView' => 'reports.activos.tables.dados-de-baja',
    ])
@endsection
