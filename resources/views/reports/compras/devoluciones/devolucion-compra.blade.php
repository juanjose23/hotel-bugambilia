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
                            <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Proveedor</strong><br>
                            <span style="font-size: 13px; font-weight: bold;">{{ $record->ordenCompraCodigo ?? 'N/A' }}</span><br>
                            @if($record->recepcionCompraCodigo)
                                <span style="font-size: 12px; color: #666;">Recepción Ref: {{ $record->recepcionCompraCodigo }}</span>
                            @endif
                        </td>
                        <td style="width: 50%; vertical-align: top; text-align: right;">
                            <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Devolución</strong><br>
                            <span style="font-size: 13px; font-weight: bold; color: #711C37;">{{ $record->codigo }}</span><br>
                            <span style="font-size: 12px; color: #666;">Fecha: {{ $record->fecha_devolucion?->format('d/m/Y') }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <div style=" background: #f1f5f9; border: 1px solid #e2e8f0; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Estado:</strong>
                <strong style="font-size: 12px;">{{ $record->estado?->label() ?? 'N/A' }}</strong>
                @if($record->ordenCompraCodigo)
                    <span style="font-size: 11px; color: #666; margin-left: 20px;">OC: {{ $record->ordenCompraCodigo }}</span>
                @endif
                @if($record->documento_externo)
                    <span style="font-size: 11px; color: #666; margin-left: 20px;">Doc. Externo: {{ $record->documento_externo }}</span>
                @endif
            </div>

            @if($record->motivo)
            <div style="background: #fef3c7; border: 1px solid #fde68a; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <strong style="color: #92400e; font-size: 11px; text-transform: uppercase;">Motivo de Devolución:</strong><br>
                <span style="font-size: 12px; color: #78350f;">{{ $record->motivo }}</span>
            </div>
            @endif

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align: center;">#</th>
                        <th>Producto</th>
                        <th style="width:80px; text-align: center;">Variante</th>
                        <th style="width:80px; text-align: center;">U. Medida</th>
                        <th style="width:80px; text-align: center;">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($record->items as $item)
                        <tr>
                            <td style="text-align:center;">{{ $loop->iteration }}</td>
                            <td><strong>{{ $item->producto?->nombre }}</strong></td>
                            <td style="text-align:center;">{{ $item->variante?->codigo ?: '—' }}</td>
                            <td style="text-align:center;">{{ $item->unidadMedida?->valor ?: '—' }}</td>
                            <td style="text-align:center; font-weight: bold;">{{ number_format($item->cantidad_devolver, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 40px;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 30%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Responsable</strong>
                        </td>
                        <td style="width: 5%;"></td>
                        <td style="width: 30%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Vo.Bo. Compras</strong>
                        </td>
                        <td style="width: 5%;"></td>
                        <td style="width: 30%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Proveedor</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="report-footer">

            @include('reports.layout.partials.footer', [
                'generadoEn' => $generadoEn ?? $fecha ?? now()->format('d/m/Y H:i'),
                'usuario' => $usuario ,
            ])
        </div>
    </div>
@endsection
