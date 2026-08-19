@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Cotización de Compra',
    'codigo' => $codigoReporte ?? 'HTB-COM-009',
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
                    <span style="font-size: 11pt; font-weight: bold;">{{ $record->proveedor?->persona?->razon_social ?? $record->proveedor?->persona?->nombre_completo }}</span><br>
                    <span style="font-size: 8.5pt; color: #64748b;">Días de Entrega: {{ $record->dias_entrega }} días</span>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Referencia Solicitud</strong><br>
                    <span style="font-size: 11pt; font-weight: bold; color: #711C37;">{{ $record->solicitud?->codigo }}</span><br>
                    <span style="font-size: 8.5pt; color: #64748b;">Fecha: {{ $record->fecha_cotizacion?->format('d/m/Y') }}</span>
                </td>
            </tr>
        </table>
    </div>

    @if($record->es_elegida)
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 8px 12px; border-radius: 4px; margin-bottom: 16px; text-align: center;">
            <strong style="color: #166534; font-size: 8.5pt; text-transform: uppercase;">Opción Seleccionada para Compra</strong>
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
                        <strong>{{ $item->producto?->nombre }}</strong>
                        @if($item->es_elegido)
                            <br><small style="color: #166534; font-weight: bold;">(Elegido)</small>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ number_format((float)$item->cantidad, 2) }}</td>
                    <td style="text-align: center;">{{ $record->moneda?->simbolo ?? '$' }}{{ number_format((float)$item->precio_unitario, 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ $record->moneda?->simbolo ?? '$' }}{{ number_format((float)$item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 16px; text-align: right;" class="avoid-break">
        <div style="display: inline-block; background: #711C37; color: #fff; padding: 10px 18px; border-radius: 4px; text-align: right;">
            <span style="font-size: 8pt; text-transform: uppercase; display: block; margin-bottom: 2px;">Total Cotizado</span>
            <span style="font-size: 14pt; font-weight: bold; display: block;">{{ $record->moneda?->simbolo ?? '$' }}{{ number_format((float)$record->total, 2) }}</span>
        </div>
    </div>

    <div style="margin-top: 35px;" class="avoid-break">
        <table style="width: 100%;">
            <tr>
                <td style="width: 45%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Representante de Ventas</strong>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Vo.Bo. Compras</strong>
                </td>
            </tr>
        </table>
    </div>
@endsection
