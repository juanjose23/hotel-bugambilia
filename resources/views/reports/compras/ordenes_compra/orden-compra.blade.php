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
                            <span style="font-size: 13px; font-weight: bold;">{{ $ordenCompra->proveedor->persona->razon_social ?? $ordenCompra->proveedor->persona->nombre_completo }}</span>
                            @if($ordenCompra->condicionPago)
                                <br><span style="font-size: 12px; color: #666;">Condición de Pago: {{ $ordenCompra->condicionPago->valor }}</span>
                            @endif
                        </td>
                        <td style="width: 50%; vertical-align: top; text-align: right;">
                            <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Orden de Compra</strong><br>
                            <span style="font-size: 13px; font-weight: bold; color: #711C37;">{{ $ordenCompra->codigo }}</span><br>
                            <span style="font-size: 12px; color: #666;">Fecha: {{ $ordenCompra->fecha_orden?->format('d/m/Y') }}</span><br>
                            <span style="font-size: 12px; color: #666;">Entrega Estimada: {{ $ordenCompra->fecha_entrega_estimada?->format('d/m/Y') ?: 'No definida' }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Estado:</strong>
                <strong style="font-size: 12px;">{{ $estadoLabel }}</strong>
                @if($ordenCompra->solicitud)
                    <span style="font-size: 11px; color: #666; margin-left: 20px;">Ref: {{ $ordenCompra->solicitud->codigo }}</span>
                @endif
                @if($ordenCompra->cotizacion)
                    <span style="font-size: 11px; color: #666; margin-left: 20px;">Cotización: #{{ $ordenCompra->cotizacion->id }}</span>
                @endif
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align: center;">#</th>
                        <th>Producto</th>
                        <th style="width:80px; text-align: center;">Variante</th>
                        <th style="width:80px; text-align: center;">U. Medida</th>
                        <th style="width:80px; text-align: center;">Cantidad</th>
                        <th style="width:90px; text-align: center;">P. Unitario</th>
                        <th style="width:90px; text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ordenCompra->items as $item)
                        <tr>
                            <td style="text-align:center;">{{ $loop->iteration }}</td>
                            <td><strong>{{ $item->producto?->nombre }}</strong></td>
                            <td style="text-align:center;">{{ $item->variante?->codigo ?: '—' }}</td>
                            <td style="text-align:center;">{{ $item->unidadMedida?->valor ?: '—' }}</td>
                            <td style="text-align:center;">{{ number_format($item->cantidad, 2) }}</td>
                            <td style="text-align:center;">{{ $simboloMoneda }}{{ number_format($item->precio_unitario, 2) }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $simboloMoneda }}{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px; text-align: right;">
                <div style="display: inline-block; background: #711C37; color: #fff; padding: 15px 25px; border-radius: 4px; text-align: right;">
                    <span style="font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 5px;">Total Orden</span>
                    <span style="font-size: 22px; font-weight: bold; display: block;">{{ $simboloMoneda }}{{ number_format($ordenCompra->total, 2) }}</span>
                    @if($ordenCompra->impuestos > 0)
                        <span style="font-size: 10px; opacity: 0.9; display: block; margin-top: 5px;">
                            Subtotal: {{ $simboloMoneda }}{{ number_format($ordenCompra->subtotal, 2) }} | Impuestos: {{ $simboloMoneda }}{{ number_format($ordenCompra->impuestos, 2) }}
                        </span>
                    @endif
                    @if($ordenCompra->tasa_cambio > 1)
                        @if($ordenCompra->cotizacion?->moneda?->codigo === 'NIO')
                            <span style="font-size: 10px; opacity: 0.9; display: block; margin-top: 5px; font-weight: bold;">
                                Equivalente: ${{ number_format($ordenCompra->total / $ordenCompra->tasa_cambio, 2) }} USD
                            </span>
                            <span style="font-size: 9px; opacity: 0.7; display: block; margin-top: 2px;">
                                Tasa de Cambio: C$ {{ number_format($ordenCompra->tasa_cambio, 4) }}
                            </span>
                        @elseif($ordenCompra->cotizacion?->moneda?->codigo === 'USD')
                            <span style="font-size: 10px; opacity: 0.9; display: block; margin-top: 5px; font-weight: bold;">
                                Equivalente: C$ {{ number_format($ordenCompra->total * $ordenCompra->tasa_cambio, 2) }} NIO
                            </span>
                            <span style="font-size: 9px; opacity: 0.7; display: block; margin-top: 2px;">
                                Tasa de Cambio: C$ {{ number_format($ordenCompra->tasa_cambio, 4) }}
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            @if($ordenCompra->notas)
            <div style="margin-top: 20px; background: #fffbeb; border: 1px solid #fde68a; padding: 10px; border-radius: 4px;">
                <strong style="color: #92400e; font-size: 11px; text-transform: uppercase;">Notas:</strong><br>
                <span style="font-size: 12px; color: #78350f;">{{ $ordenCompra->notas }}</span>
            </div>
            @endif

            <div style="margin-top: 40px;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 30%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Solicitante</strong>
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
                'usuario' => $usuario ?? 'Sistema',
            ])
        </div>
    </div>
@endsection
