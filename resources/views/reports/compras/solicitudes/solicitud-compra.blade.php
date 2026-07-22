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

        @if(!empty($barcodeBase64))
        <div style="text-align: right; margin-top: 5px;">
            <img src="{{ $barcodeBase64 }}" style="height: 55px;" alt="Código de barras">
        </div>
        @endif

        <div class="report-content">
            <div style="margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Solicitante</strong><br>
                            <span style="font-size: 13px; font-weight: bold;">{{ $solicitud->colaborador?->codigo }} - {{ $solicitud->colaborador?->persona?->nombre_completo }}</span><br>
                            <span style="font-size: 12px; color: #666;">Departamento: {{ $solicitud->departamentoSolicitante?->nombre }}</span>
                        </td>
                        <td style="width: 50%; vertical-align: top; text-align: right;">
                            <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Referencia</strong><br>
                            <span style="font-size: 13px; font-weight: bold; color: #711C37;">{{ $solicitud->codigo }}</span><br>
                            <span style="font-size: 12px; color: #666;">Fecha: {{ $solicitud->fecha_solicitud?->format('d/m/Y') }}</span><br>
                            <span style="font-size: 12px; color: #666;">Necesita: {{ $solicitud->fecha_necesita?->format('d/m/Y') ?: 'No definida' }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Estado:</strong>
                <strong style="font-size: 12px;">{{ $estadoLabel }}</strong>
            </div>

            @if ($solicitud->motivo)
                <div style="margin-bottom: 16px;">
                    <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Motivo:</strong><br>
                    <span style="font-size: 12px;">{{ $solicitud->motivo }}</span>
                </div>
            @endif

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align: center;">#</th>
                        <th>Producto</th>
                        <th style="width:80px; text-align: center;">Variante</th>
                        <th style="width:90px; text-align: center;">Cant. Solicitada</th>
                        <th style="width:90px; text-align: center;">Cant. Aprobada</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($solicitud->items as $item)
                        <tr>
                            <td style="text-align:center;">{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $item->producto?->nombre }}</strong>
                            </td>
                            <td style="text-align:center;">{{ $item->productoVariante?->codigo ?: '—' }}</td>
                            <td style="text-align:center;">{{ number_format($item->cantidad_solicitada, 2) }}</td>
                            <td style="text-align:center;">{{ $item->cantidad_aprobada > 0 ? number_format($item->cantidad_aprobada, 2) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($solicitud->notas)
                <div style="margin-top: 20px; padding: 10px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 4px;">
                    <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Notas:</strong><br>
                    <span style="font-size: 11px;">{{ $solicitud->notas }}</span>
                </div>
            @endif

            <div style="margin-top: 40px;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 45%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Solicitante</strong>
                        </td>
                        <td style="width: 10%;"></td>
                        <td style="width: 45%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Vo.Bo. Compras</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'generadoEn' => $generadoEn ?? $fecha ?? now()->format('d/m/Y H:i'),
                'usuario' => $usuario ?? 'Sistema',
            ])
        </div>
    </div>
@endsection
