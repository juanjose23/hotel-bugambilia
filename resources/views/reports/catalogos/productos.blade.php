@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Catalogo de Productos',
    'codigo' => $codigoReporte ?? 'HTB-CAT-001',
])

@section('extra-css')
    .col-sku { width: 20%; }
    .col-nombre { width: 35%; }
    .col-desc { width: 45%; }
    .row-variant td { background: #f8fafc !important; font-size: 8pt; }
    .row-variant td:first-child { padding-left: 14px; }
    .badge-count { display: inline-block; font-size: 7.5pt; color: #711C37; font-weight: bold; margin-left: 4px; }
@endsection

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas ?? [$items ?? collect()],
        'tableView' => 'reports.catalogos.tables.productos',
    ])
@endsection
