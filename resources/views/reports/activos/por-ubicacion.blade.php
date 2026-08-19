@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Reporte de Activos por Ubicación',
    'codigo' => $codigoReporte ?? 'HTB-ACT-005',
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
    .empty-row { text-align: center; color: #64748b; padding: 14px; }
@endsection

@section('content')
    @forelse($ubicaciones as $ubicacion)
        <div style="margin-bottom: 20px;">
            @include('reports.activos.partials.section-bar', [
                'titulo' => $ubicacion['tipo'] . ': ' . $ubicacion['nombre'],
                'subtitulo' => count($ubicacion['activos']) . ' activos — Subtotal: ' . $ubicacion['moneda'] . number_format($ubicacion['subtotal'], 2),
            ])
            <table class="data-table" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Código Inventario</th>
                        <th>Producto</th>
                        <th style="text-align: center;">Estado</th>
                        <th style="text-align: right;">Costo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ubicacion['activos'] as $activo)
                        <tr>
                            <td>@include('reports.activos.partials.sku', ['codigo' => $activo->codigo_inventario])</td>
                            <td><strong>{{ $activo->producto?->nombre ?? '—' }}</strong></td>
                            <td style="text-align: center;">
                                @include('reports.activos.partials.estado-activo', ['estado' => $activo->estado])
                            </td>
                            <td style="text-align: right;">
                                @include('reports.activos.partials.costo', ['monto' => $activo->costo_adquisicion, 'monedaSimbolo' => $activo->moneda?->simbolo])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        @include('reports.activos.partials.empty-state', ['type' => 'div', 'mensaje' => 'No se encontraron activos asignados a ubicaciones.'])
    @endforelse

    @if(count($ubicaciones) > 0)
        @php $granTotal = array_sum(array_column($ubicaciones, 'subtotal')); @endphp
        <div style="margin-top: 16px; padding: 10px 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; text-align: right;" class="avoid-break">
            <strong style="color: #711C37; font-size: 9pt; text-transform: uppercase;">Valor Total en Ubicaciones:</strong>
            <strong style="color: #711C37; font-size: 11pt;">${{ number_format($granTotal, 2) }}</strong>
        </div>
    @endif
@endsection
