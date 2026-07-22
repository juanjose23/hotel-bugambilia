@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])
@section('content')
    <div class="pagina">
        <div class="report-header">
            @include('reports.layout.partials.header', [
                'logo_base64' => $datosHotel['logo_base64'] ?? null,
                'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
            ])
        </div>

        <div class="report-content">
            <div style="margin-bottom:20px;">
                <table style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td style="width:50%;vertical-align:top;border:none;padding:0;">
                            <strong style="color:#711C37;font-size:11px;text-transform:uppercase;">Ficha de Mantenimiento</strong><br>
                            <span style="font-size:13px;font-weight:bold;">{{ $record->activo?->codigo_inventario ?? '—' }}</span><br>
                            <span style="font-size:11px;color:#555;">{{ $record->activo?->producto?->nombre ?? '—' }}</span>
                        </td>
                        <td style="width:50%;vertical-align:top;border:none;padding:0;text-align:right;">
                            @include('reports.activos.partials.estado-activo', ['estado' => $record->estado, 'scope' => 'mantenimiento', 'style' => 'font-size:9px;'])
                        </td>
                    </tr>
                </table>
            </div>

            @include('reports.activos.partials.info-grid', [
                'campos' => [
                    ['label' => 'Tipo', 'value' => $record->tipo?->label()],
                    ['label' => 'Fecha Programada', 'value' => $record->fecha_programada?->format('d/m/Y')],
                    ['label' => 'Fecha Realizada', 'value' => $record->fecha_realizada?->format('d/m/Y') ?? 'Pendiente'],
                    ['label' => 'Plan / Descripción', 'value' => $record->plan?->descripcion],
                    ['label' => 'Proveedor', 'value' => $record->plan?->proveedor?->persona?->primer_nombre ?? 'Taller interno'],
                    ['label' => 'Costo Real', 'value' => $record->costo_real ?? 0, 'isCosto' => true, 'monedaSimbolo' => $record->plan?->moneda?->simbolo],
                    ['label' => 'Realizado Por', 'value' => $record->realizadoPor?->name],
                    ['label' => 'Observaciones', 'value' => $record->observaciones ?? 'Sin observaciones'],
                ],
            ])
        </div>

        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'generadoEn' => $generadoEn ?? now()->format('d/m/Y H:i'),
                'usuario' => $usuario ?? 'Sistema',
            ])
        </div>
    </div>
@endsection
