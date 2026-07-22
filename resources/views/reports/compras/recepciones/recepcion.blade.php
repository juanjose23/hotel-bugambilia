@php use App\Enums\Compras\EstadoRecepcion; @endphp

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
            <!-- Información General -->
            <div style="margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Orden de Compra Ref.</strong><br>
                            <span style="font-size: 13px; font-weight: bold; color: #711C37;">{{ $record->ordenCompra->codigo }}</span><br>
                            <span style="font-size: 12px; color: #666;">Proveedor: {{ $record->ordenCompra->proveedor->persona->razon_social ?? $record->ordenCompra->proveedor->persona->nombre_completo }}</span>
                        </td>
                        <td style="width: 50%; vertical-align: top; text-align: right;">
                            <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Detalles Recepción</strong><br>
                            <span style="font-size: 13px; font-weight: bold;">Fecha: {{ $record->fecha_recepcion->format('d/m/Y') }}</span><br>
                            <span class="badge {{ $record->estado === EstadoRecepcion::Completa ? 'badge-on' : 'badge-off' }}">
                                {{ $record->estado?->label() ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Notas -->
            @if($record->notas)
            <div style="background: #f8fafc; padding: 15px; border: 1px solid #e2e8f0; margin-bottom: 20px; border-radius: 4px;">
                <strong style="color: #711C37; font-size: 11px; text-transform: uppercase;">Observaciones de Almacén</strong><br>
                <p style="font-style: italic; color: #4a5568; margin-top: 5px;">"{{ $record->notas }}"</p>
            </div>
            @endif

            <!-- Tabla de Recepción -->
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
                            <strong>{{ $item->producto->nombre }}</strong>
                            @if($item->variante)
                                <br><span style="font-size: 8px; color: #666;">Variante: {{ $item->variante->codigo }}</span>
                            @endif
                            <br><small style="color: #999; text-transform: uppercase;">{{ $item->unidadMedida->nombre ?? 'Unidad' }}</small>
                        </td>
                        <td style="text-align: center;">{{ number_format($cantidadOrdenada, 2) }}</td>
                        <td style="text-align: center; font-weight: bold; color: #166534;">{{ number_format($item->cantidad_recibida, 2) }}</td>
                        <td style="text-align: center; font-weight: bold; color: #991b1b;">{{ number_format($item->cantidad_rechazada, 2) }}</td>
                        <td style="text-align: right; color: {{ $diferencia > 0 ? '#b45309' : '#999' }};">
                            {{ $diferencia > 0 ? '-' . number_format($diferencia, 2) : 'OK' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Firmas -->
            <div style="margin-top: 40px;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 45%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Entregado por (Proveedor)</strong>
                        </td>
                        <td style="width: 10%;"></td>
                        <td style="width: 45%; text-align: center; border-top: 1px dashed #ccc; padding-top: 10px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Recibido Conforme (Almacén)</strong><br>
                            <span style="font-size: 11px; font-weight: bold;">{{ $record->receptor->name }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'generadoEn' => now()->format('d/m/Y H:i'),
                'usuario' => 'Sistema',
            ])
        </div>
    </div>
@endsection
