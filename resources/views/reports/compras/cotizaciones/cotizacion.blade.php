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
                            <span style="font-size: 13px; font-weight: bold;">{{ $record->proveedor->persona->razon_social ?? $record->proveedor->persona->nombre_completo }}</span><br>
                            <span style="font-size: 12px; color: #666;">Días de Entrega: {{ $record->dias_entrega }} días</span>
                        </td>
                        <td style="width: 50%; vertical-align: top; text-align: right;">
                            <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Referencia</strong><br>
                            <span style="font-size: 13px; font-weight: bold; color: #711C37;">{{ $record->solicitud->codigo }}</span><br>
                            <span style="font-size: 12px; color: #666;">Fecha: {{ $record->fecha_cotizacion->format('d/m/Y') }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            @if($record->es_elegida)
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
                <strong style="color: #166534; font-size: 12px; text-transform: uppercase;">Opción Seleccionada para Compra</strong>
            </div>
            @endif

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto / Variante</th>
                        <th style="text-align: center;">Cant.</th>
                        <th style="text-align: center;">P. Unit.</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($record->items as $item)
                    <tr @if($item->es_elegido) style="background: #f0fdf4;" @endif>
                        <td>
                            <strong>{{ $item->producto->nombre }}</strong>
                            @if($item->es_elegido)
                            <br><small style="color: #166534; font-weight: bold;">(Elegido)</small>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ number_format($item->cantidad, 2) }}</td>
                        <td style="text-align: center;">{{ $record->moneda?->simbolo ?? '$' }}{{ number_format($item->precio_unitario, 2) }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ $record->moneda?->simbolo ?? '$' }}{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px; text-align: right;">
                <div style="display: inline-block; background: #711C37; color: #fff; padding: 15px 25px; border-radius: 4px; text-align: right;">
                    <span style="font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 5px;">Total Cotizado</span>
                    <span style="font-size: 22px; font-weight: bold; display: block;">{{ $record->moneda?->simbolo ?? '$' }}{{ number_format($record->total, 2) }}</span>
                    @if($record->tasa_cambio > 1)
                        @if($record->moneda?->codigo === 'NIO')
                            <span style="font-size: 10px; opacity: 0.9; display: block; margin-top: 5px; font-weight: bold;">
                                Equivalente: ${{ number_format($record->total / $record->tasa_cambio, 2) }} USD
                            </span>
                            <span style="font-size: 9px; opacity: 0.7; display: block; margin-top: 2px;">
                                Tasa de Cambio: C$ {{ number_format($record->tasa_cambio, 4) }}
                            </span>
                        @elseif($record->moneda?->codigo === 'USD')
                            <span style="font-size: 10px; opacity: 0.9; display: block; margin-top: 5px; font-weight: bold;">
                                Equivalente: C$ {{ number_format($record->total * $record->tasa_cambio, 2) }} NIO
                            </span>
                            <span style="font-size: 9px; opacity: 0.7; display: block; margin-top: 2px;">
                                Tasa de Cambio: C$ {{ number_format($record->tasa_cambio, 4) }}
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            <div style="margin-top: 40px;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 45%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Representante de Ventas</strong>
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
