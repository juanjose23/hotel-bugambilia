@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])
@section('content')
@foreach($paginas as $i => $items)
    <div class="pagina">
        <div class="report-header">
            @include('reports.layout.partials.header', [
                'logo_base64' => $datosHotel['logo_base64'] ?? null,
                'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
            ])
        </div>
        <div class="report-content">
            <!-- Filtros / Rango -->
            <div style="margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 4px; border: 1px solid #e2e8f0;">
                <span style="font-size: 9px; color: #711C37; font-weight: bold; text-transform: uppercase;">Período de Recepciones:</span>
                <span style="font-size: 11px; font-weight: bold; margin-left: 10px;">{{ $fechaInicio ?? 'Histórico' }} — {{ $fechaFin ?? 'Hoy' }}</span>
            </div>

            <!-- Tabla de Tiempos de Entrega -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th style="text-align: center;">Órdenes Completadas</th>
                        <th style="text-align: center;">Promedio Días de Entrega (Lead Time)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><strong>{{ $item->proveedor_nombre }}</strong></td>
                            <td style="text-align: center;">{{ $item->ordenes_recibidas }}</td>
                            <td style="text-align: center; font-weight: bold; color: #711C37;">
                                {{ number_format((float) $item->promedio_dias, 1) }} días
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #666; padding: 20px;">
                                No se registraron recepciones físicas en el período seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Nota del Reporte -->
            <div style="margin-top: 30px; background: #fffdf5; padding: 12px; border-radius: 4px; border: 1px solid #fef08a; font-size: 10px; color: #854d0e; line-height: 1.5;">
                <strong>Nota de Rendimiento:</strong> El tiempo de entrega (Lead Time) se calcula como la cantidad de días transcurridos entre la emisión de la Orden de Compra y la Recepción Física del pedido en bodega. Un promedio menor representa mayor eficiencia por parte del proveedor.
            </div>
        </div>
        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'generadoEn' => now()->format('d/m/Y H:i'),
                'usuario' => 'Sistema',
            ])
        </div>
    </div>
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
@endsection
