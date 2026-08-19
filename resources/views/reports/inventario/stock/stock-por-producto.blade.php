@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('extra-css')
    .stock-producto-table .col-producto { width: 22%; }
    .stock-producto-table .col-variante { width: 18%; }
    .stock-producto-table .col-categoria { width: 15%; }
    .stock-producto-table .col-ubicacion { width: 19%; }
    .stock-producto-table .col-numero { width: 9%; }
    .stock-producto-table .col-lotes { width: 8%; }
@endsection

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'tableData' => [
            'totalStock' => $totalStock ?? 0,
            'totalCuarentena' => $totalCuarentena ?? 0,
            'totalRegistros' => $totalRegistros,
        ],
        'tableView' => 'reports.inventario.stock.tables.stock-por-producto',
    ])
@endsection
