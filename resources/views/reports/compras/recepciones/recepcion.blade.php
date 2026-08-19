@php use App\Enums\Compras\EstadoRecepcion; @endphp

@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Recepción de Mercancía',
    'codigo' => $codigoReporte ?? 'HTB-COM-012',
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
                    <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Orden de Compra Ref.</strong><br>
                    <span style="font-size: 11pt; font-weight: bold; color: #711C37;">{{ $record->ordenCompra?->codigo }}</span><br>
                    <span style="font-size: 8.5pt; color: #64748b;">Proveedor: {{ $record->ordenCompra?->proveedor?->persona?->razon_social ?? $record->ordenCompra?->proveedor?->persona?->nombre_completo ?? '—' }}</span>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Detalles Recepción</strong><br>
                    <span style="font-size: 10pt; font-weight: bold;">Fecha: {{ optional($record->fecha_recepcion)->format('d/m/Y') }}</span><br>
                    <span class="badge {{ $record->estado === EstadoRecepcion::Completa ? 'badge-success' : 'badge-warning' }}">
                        {{ $record->estado?->label() ?? 'N/A' }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    @if($record->notas)
        <div style="background: #f8fafc; padding: 10px 12px; border: 1px solid #e2e8f0; margin-bottom: 16px; border-radius: 4px;">
            <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Observaciones de Almacén</strong><br>
            <p style="font-style: italic; color: #475569; margin-top: 3px; font-size: 8.5pt;">"{{ $record->notas }}"</p>
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th style="text-align: center;">Ordenado</th>
                <th style="text-align: center;">Recibido</th>
                <th style="text-align: center;">Rechazado</th>
                <th style="text-align: right;">Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->items as $item)
                @php
                    $cantidadOrdenada = $item->ordenItem?->cantidad ?? 0;
                    $diferencia = $cantidadOrdenada - $item->cantidad_recibida;
                @endphp
                <tr>
                    <td>
                        <strong>{{ $item->producto?->nombre }}</strong>
                        @if($item->variante)
                            <br><span style="font-size: 7.5pt; color: #64748b;">Variante: {{ $item->variante->codigo }}</span>
                        @endif
                        <br><small style="color: #94a3b8; text-transform: uppercase;">{{ $item->unidadMedida?->valor ?? $item->unidadMedida?->nombre ?? 'Unidad' }}</small>
                    </td>
                    <td style="text-align: center;">{{ number_format((float)$cantidadOrdenada, 2) }}</td>
                    <td style="text-align: center; font-weight: bold; color: #047857;">{{ number_format((float)$item->cantidad_recibida, 2) }}</td>
                    <td style="text-align: center; font-weight: bold; color: #b91c1c;">{{ number_format((float)$item->cantidad_rechazada, 2) }}</td>
                    <td style="text-align: right; color: {{ $diferencia > 0 ? '#b45309' : '#64748b' }};">
                        {{ $diferencia > 0 ? '-' . number_format((float)$diferencia, 2) : 'OK' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 35px;" class="avoid-break">
        <table style="width: 100%;">
            <tr>
                <td style="width: 45%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Entregado por (Proveedor)</strong>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Recibido Conforme (Almacén)</strong><br>
                    <span style="font-size: 8.5pt; font-weight: bold;">{{ $record->receptor?->name }}</span>
                </td>
            </tr>
        </table>
    </div>
@endsection
