@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Análisis de Tiempos de Entrega',
    'codigo' => $codigoReporte ?? 'HTB-COM-014',
    'fechaInicio' => $fechaInicio ?? null,
    'fechaFin' => $fechaFin ?? null,
])

@section('content')
    @include('reports.layout.partials.paginated-table', [
        'paginas' => $paginas,
        'datosHotel' => $datosHotel,
        'fechaInicio' => $fechaInicio ?? null,
        'fechaFin' => $fechaFin ?? null,
        'tableView' => 'reports.compras.analisis.tables.tiempos-entrega',
    ])

    <div style="margin-top: 16px; background: #fffdf5; padding: 10px 12px; border-radius: 4px; border: 1px solid #fef08a; font-size: 8pt; color: #854d0e;" class="avoid-break">
        <strong>Nota de Rendimiento:</strong> El tiempo de entrega (Lead Time) se calcula como la cantidad de días transcurridos entre la emisión de la Orden de Compra y la Recepción Física del pedido en bodega.
    </div>
@endsection
