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

            {{-- PASO 1: SOLICITUD --}}
            <div style="background:#711C37;color:white;padding:6px 12px;font-size:10px;font-weight:bold;border-radius:3px;margin-bottom:8px;">
                PASO 1 — SOLICITUD DE COMPRA (HTB-COM-001): {{ $solicitud->codigo ?? 'N/A' }}
            </div>
            <table style="width:100%;font-size:9.5px;margin-bottom:12px;">
                <tr>
                    <td style="width:25%;color:#555;">Departamento:</td>
                    <td style="font-weight:bold;">{{ $solicitud->departamento ?? '—' }}</td>
                    <td style="width:25%;color:#555;">Solicitante:</td>
                    <td style="font-weight:bold;">{{ $solicitud->solicitante ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="color:#555;">Fecha Solicitud:</td>
                    <td>{{ $solicitud->fecha_solicitud ? \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y') : '—' }}</td>
                    <td style="color:#555;">Estado:</td>
                    <td style="font-weight:bold;">{{ $solicitud->estado ?? '—' }}</td>
                </tr>
                @if(!empty($solicitud->motivo))
                <tr><td style="color:#555;">Motivo:</td><td colspan="3">{{ $solicitud->motivo }}</td></tr>
                @endif
            </table>

            @if(!empty($solicitud->items))
            <table class="data-table" style="margin-bottom:16px;">
                <thead><tr><th>Producto</th><th>Variante</th><th style="text-align:center;">Cant. Solicitada</th><th style="text-align:center;">Cant. Aprobada</th></tr></thead>
                <tbody>
                    @foreach($solicitud->items as $item)
                    <tr>
                        <td>{{ $item->producto ?? '—' }}</td>
                        <td style="font-size:8.5px;color:#666;">{{ $item->variante ?? 'Sin variante' }}</td>
                        <td style="text-align:center;">{{ number_format((float)$item->cantidad_solicitada, 2) }}</td>
                        <td style="text-align:center;font-weight:bold;">{{ number_format((float)$item->cantidad_aprobada, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            {{-- PASO 2: COTIZACIONES --}}
            <div style="background:#1e40af;color:white;padding:6px 12px;font-size:10px;font-weight:bold;border-radius:3px;margin-bottom:8px;">
                PASO 2 — COTIZACIONES RECIBIDAS (HTB-COM-002)
            </div>
            @if(empty($cotizaciones))
            <p style="font-size:9px;color:#888;margin-bottom:12px;">No se registraron cotizaciones.</p>
            @else
            <table class="data-table" style="margin-bottom:16px;">
                <thead><tr><th>Proveedor</th><th style="text-align:right;">Total Ofertado</th><th style="text-align:center;">Días Entrega</th><th style="text-align:center;">Elegida</th></tr></thead>
                <tbody>
                    @foreach($cotizaciones as $cot)
                    <tr style="{{ $cot->es_elegida ? 'background:#f0fdf4;' : '' }}">
                        <td>{{ $cot->proveedor_nombre ?? '—' }}</td>
                        <td style="text-align:right;font-weight:bold;">${{ number_format((float)$cot->total, 2) }}</td>
                        <td style="text-align:center;">{{ $cot->tiempo_entrega_dias ?? '—' }}</td>
                        <td style="text-align:center;color:{{ $cot->es_elegida ? '#16a34a' : '#999' }};font-weight:bold;">{{ $cot->es_elegida ? '★ SÍ' : 'No' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            {{-- PASO 3: ÓRDENES DE COMPRA --}}
            <div style="background:#065f46;color:white;padding:6px 12px;font-size:10px;font-weight:bold;border-radius:3px;margin-bottom:8px;">
                PASO 3 — ÓRDENES DE COMPRA EMITIDAS (HTB-COM-003)
            </div>
            @if(empty($ordenesCompra))
            <p style="font-size:9px;color:#888;margin-bottom:12px;">No se emitieron órdenes de compra.</p>
            @else
            @foreach($ordenesCompra as $orden)
            <table style="width:100%;font-size:9.5px;margin-bottom:6px;">
                <tr>
                    <td style="width:20%;color:#555;">Orden:</td><td style="font-weight:bold;">{{ $orden->codigo }}</td>
                    <td style="width:20%;color:#555;">Fecha:</td><td>{{ $orden->fecha_orden ? \Carbon\Carbon::parse($orden->fecha_orden)->format('d/m/Y') : '—' }}</td>
                    <td style="width:20%;color:#555;">Proveedor:</td><td style="font-weight:bold;">{{ $orden->proveedor_nombre }}</td>
                </tr>
                <tr>
                    <td style="color:#555;">Estado OC:</td><td style="font-weight:bold;">{{ $orden->estado }}</td>
                    <td style="color:#555;">Condición Pago:</td><td>{{ $orden->condicion_pago ?? 'Contado' }}</td>
                    <td style="color:#555;">Total OC:</td><td style="font-weight:bold;color:#711C37;">${{ number_format((float)$orden->total, 2) }}</td>
                </tr>
            </table>

            @if(!empty($orden->recepciones))
            <div style="padding-left:16px;border-left:3px solid #16a34a;margin-bottom:12px;">
                <div style="font-size:9px;font-weight:bold;color:#065f46;margin-bottom:4px;">▶ RECEPCIONES EN BODEGA (HTB-COM-004):</div>
                <table class="data-table">
                    <thead><tr><th>Código</th><th>Factura/Ref.</th><th style="text-align:center;">Fecha Recepción</th><th>Estado</th><th>Receptor</th></tr></thead>
                    <tbody>
                        @foreach($orden->recepciones as $rec)
                        <tr>
                            <td><strong>{{ $rec->codigo }}</strong></td>
                            <td style="font-size:8.5px;">{{ $rec->factura_referencia ?? '—' }}</td>
                            <td style="text-align:center;">{{ $rec->fecha_recepcion ? \Carbon\Carbon::parse($rec->fecha_recepcion)->format('d/m/Y') : '—' }}</td>
                            <td style="font-size:8.5px;">{{ $rec->estado }}</td>
                            <td style="font-size:8.5px;">{{ $rec->receptor ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            @endforeach
            @endif

        </div>
        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'generadoEn' => now()->format('d/m/Y H:i'),
                'usuario' => 'Sistema',
            ])
        </div>
    </div>
@endsection
