@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Orden de Compra',
    'codigo' => $codigoReporte ?? 'HTB-COM-011',
])

@section('content')
    @if(!empty($barcodeBase64))
        <div style="text-align: right; margin-bottom: 8px;">
            <img src="{{ $barcodeBase64 }}" style="height: 45px;" alt="Código de barras">
        </div>
    @endif

    <div style="margin-bottom: 16px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Proveedor</strong><br>
                    <span style="font-size: 11pt; font-weight: bold;">{{ $ordenCompra->proveedor?->persona?->razon_social ?? $ordenCompra->proveedor?->persona?->nombre_completo }}</span>
                    @if($ordenCompra->condicionPago)
                        <br><span style="font-size: 8.5pt; color: #64748b;">Condición de Pago: {{ $ordenCompra->condicionPago->valor }}</span>
                    @endif
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Orden de Compra</strong><br>
                    <span style="font-size: 11pt; font-weight: bold; color: #711C37;">{{ $ordenCompra->codigo }}</span><br>
                    <span style="font-size: 8.5pt; color: #64748b;">Fecha: {{ $ordenCompra->fecha_orden?->format('d/m/Y') }}</span><br>
                    <span style="font-size: 8.5pt; color: #64748b;">Entrega Estimada: {{ $ordenCompra->fecha_entrega_estimada?->format('d/m/Y') ?: 'No definida' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 4px; margin-bottom: 16px;">
        <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Estado:</strong>
        <strong style="font-size: 8.5pt;">{{ $estadoLabel }}</strong>
        @if($ordenCompra->solicitud)
            <span style="font-size: 8pt; color: #64748b; margin-left: 16px;">Ref: {{ $ordenCompra->solicitud->codigo }}</span>
        @endif
        @if($ordenCompra->cotizacion)
            <span style="font-size: 8pt; color: #64748b; margin-left: 16px;">Cotización: #{{ $ordenCompra->cotizacion->id }}</span>
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
                    <td style="text-align:center;">{{ number_format((float)$item->cantidad, 2) }}</td>
                    <td style="text-align:center;">{{ $simboloMoneda }}{{ number_format((float)$item->precio_unitario, 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ $simboloMoneda }}{{ number_format((float)$item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 16px; text-align: right;" class="avoid-break">
        <div style="display: inline-block; background: #711C37; color: #fff; padding: 10px 18px; border-radius: 4px; text-align: right;">
            <span style="font-size: 8pt; text-transform: uppercase; display: block; margin-bottom: 2px;">Total Orden</span>
            <span style="font-size: 14pt; font-weight: bold; display: block;">{{ $simboloMoneda }}{{ number_format((float)$ordenCompra->total, 2) }}</span>
            @if($ordenCompra->impuestos > 0)
                <span style="font-size: 7.5pt; opacity: 0.9; display: block; margin-top: 2px;">
                    Subtotal: {{ $simboloMoneda }}{{ number_format((float)$ordenCompra->subtotal, 2) }} | Impuestos: {{ $simboloMoneda }}{{ number_format((float)$ordenCompra->impuestos, 2) }}
                </span>
            @endif
        </div>
    </div>

    @if($ordenCompra->notas)
        <div style="margin-top: 16px; background: #fffbeb; border: 1px solid #fde68a; padding: 8px 12px; border-radius: 4px;" class="avoid-break">
            <strong style="color: #92400e; font-size: 8pt; text-transform: uppercase;">Notas:</strong><br>
            <span style="font-size: 8.5pt; color: #78350f;">{{ $ordenCompra->notas }}</span>
        </div>
    @endif

    <div style="margin-top: 35px;" class="avoid-break">
        <table style="width: 100%;">
            <tr>
                <td style="width: 30%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Solicitante</strong>
                </td>
                <td style="width: 5%;"></td>
                <td style="width: 30%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Vo.Bo. Compras</strong>
                </td>
                <td style="width: 5%;"></td>
                <td style="width: 30%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Proveedor</strong>
                </td>
            </tr>
        </table>
    </div>
@endsection
