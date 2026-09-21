@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte ?? 'Factura Fiscal Oficial',
    'codigoReporte' => $codigoReporte ?? 'HTB-FAC-001',
    'datosHotel' => $datosHotel ?? [],
    'logo_base64' => $datosHotel['logo_base64'] ?? null,
    'hotelInfo' => $datosHotel['hotelInfo'] ?? [],
])

@section('extra-css')
    .fiscal-header-box {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .fiscal-card {
        border: 1px solid #c5d0df;
        border-radius: 4px;
        padding: 8px 10px;
        background-color: #f8fafc;
    }
    .fiscal-title {
        color: #711C37;
        font-size: 8pt;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 4px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 2px;
    }
    .fiscal-number {
        font-size: 13pt;
        font-weight: bold;
        color: #711C37;
        font-family: 'Courier New', monospace;
    }
    .watermark-anulada {
        position: fixed;
        top: 30%;
        left: 10%;
        width: 80%;
        text-align: center;
        font-size: 65pt;
        font-weight: bold;
        color: rgba(220, 38, 38, 0.16);
        border: 7px dashed rgba(220, 38, 38, 0.22);
        padding: 20px;
        transform: rotate(-25deg);
        z-index: 9999;
        text-transform: uppercase;
    }
    .totales-wrapper {
        margin-top: 10px;
        width: 100%;
        border-collapse: collapse;
        page-break-inside: avoid;
    }
    .letras-box {
        background-color: #f8fafc;
        border: 1px solid #c5d0df;
        border-radius: 4px;
        padding: 8px 10px;
        font-size: 8pt;
        color: #334155;
    }
    .totales-box {
        background: #711C37;
        color: #ffffff;
        border-radius: 4px;
        padding: 10px 14px;
        text-align: right;
    }
    .totales-table {
        width: 100%;
        border-collapse: collapse;
        color: #ffffff;
    }
    .totales-table td {
        padding: 2px 0;
        font-size: 8pt;
    }
    .totales-table .total-principal td {
        border-top: 1px solid rgba(255, 255, 255, 0.35);
        padding-top: 6px;
        margin-top: 4px;
        font-size: 12pt;
        font-weight: bold;
    }
    .legal-dgi-footer {
        margin-top: 15px;
        padding-top: 8px;
        border-top: 1px dashed #cbd5e1;
        text-align: center;
        font-size: 7pt;
        color: #64748b;
        page-break-inside: avoid;
    }
@endsection

@section('content')
    @if($factura->estado === \App\Enums\Facturacion\EstadoFactura::Anulada)
        <div class="watermark-anulada">ANULADA</div>
    @endif

    {{-- Bloque de Datos del Cliente y Datos Fiscales DGI --}}
    <table class="fiscal-header-box">
        <tr>
            {{-- Columna Izquierda: Datos del Receptor / Cliente --}}
            <td style="width: 54%; vertical-align: top; padding-right: 6px;">
                <div class="fiscal-card">
                    <div class="fiscal-title">Receptor / Cliente</div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                        <tr>
                            <td style="width: 28%; color: #64748b; padding: 2px 0;"><strong>Razón Social:</strong></td>
                            <td style="font-weight: bold; color: #0f172a; padding: 2px 0;">
                                {{ $datosReceptor['razon_social'] ?? ($factura->cliente?->nombre_completo ?? 'CONSUMIDOR FINAL') }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 2px 0;"><strong>RUC / Cédula:</strong></td>
                            <td style="padding: 2px 0; font-family: 'Courier New', monospace; font-weight: bold;">
                                {{ $datosReceptor['ruc'] ?? ($factura->cliente?->persona?->numero_identificacion ?? '—') }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 2px 0;"><strong>Teléfono:</strong></td>
                            <td style="padding: 2px 0; color: #334155;">
                                {{ $factura->cliente?->persona?->telefono ?? '—' }}
                            </td>
                        </tr>
                        @if($factura->venta)
                            <tr>
                                <td style="color: #64748b; padding: 2px 0;"><strong>Ref. Venta:</strong></td>
                                <td style="padding: 2px 0; color: #711C37; font-weight: bold;">
                                    {{ $factura->venta->numero_venta }}
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </td>

            {{-- Columna Derecha: Datos de la Factura y Autorización DGI --}}
            <td style="width: 46%; vertical-align: top; padding-left: 6px;">
                <div class="fiscal-card">
                    <div class="fiscal-title">Comprobante Fiscal DGI</div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;"><strong>N° Factura:</strong></td>
                            <td style="text-align: right; padding: 1px 0;">
                                <span class="fiscal-number">{{ $factura->numero }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;"><strong>Fecha Emisión:</strong></td>
                            <td style="text-align: right; padding: 1px 0;">
                                {{ $fechaEmision }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;"><strong>Condición / Tipo:</strong></td>
                            <td style="text-align: right; padding: 1px 0; font-weight: bold;">
                                {{ $factura->tipo->getLabel() }}
                            </td>
                        </tr>
                        @if($factura->serie)
                            <tr>
                                <td style="color: #64748b; padding: 1px 0;"><strong>Serie / Aut. DGI:</strong></td>
                                <td style="text-align: right; padding: 1px 0; font-size: 7.5pt;">
                                    Serie {{ $factura->serie->codigo }}
                                    @if($factura->numero_autorizacion_dgi || $factura->autorizacionDgi?->numero_autorizacion)
                                        · Aut. {{ $factura->numero_autorizacion_dgi ?? $factura->autorizacionDgi?->numero_autorizacion }}
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @if($factura->rango_autorizado_desde && $factura->rango_autorizado_hasta)
                            <tr>
                                <td style="color: #64748b; padding: 1px 0;"><strong>Rango Autorizado:</strong></td>
                                <td style="text-align: right; padding: 1px 0; font-size: 7.5pt; font-family: monospace;">
                                    {{ $factura->rango_autorizado_desde }} al {{ $factura->rango_autorizado_hasta }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td style="color: #64748b; padding: 1px 0;"><strong>Estado:</strong></td>
                            <td style="text-align: right; padding: 1px 0;">
                                @if($factura->estado === \App\Enums\Facturacion\EstadoFactura::Emitida)
                                    <span class="badge badge-success">EMITIDA</span>
                                @elseif($factura->estado === \App\Enums\Facturacion\EstadoFactura::Anulada)
                                    <span class="badge badge-danger">ANULADA</span>
                                @else
                                    <span class="badge badge-info">{{ $factura->estado->getLabel() }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Tabla Oficial de Detalles (.data-table uniforme) --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 32px; text-align: center;">#</th>
                <th>Descripción del Bien o Servicio</th>
                <th style="width: 55px; text-align: center;">Cant.</th>
                <th style="width: 85px; text-align: right;">Precio Unit.</th>
                <th style="width: 80px; text-align: right;">IVA (15%)</th>
                <th style="width: 90px; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($factura->detalles as $detalle)
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $loop->iteration }}</td>
                    <td>
                        <strong style="color: #1e293b;">{{ $detalle->concepto }}</strong>
                    </td>
                    <td style="text-align: center;">{{ number_format((float) $detalle->cantidad, 2) }}</td>
                    <td style="text-align: right;">{{ $simboloMoneda }} {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                    <td style="text-align: right;">{{ $simboloMoneda }} {{ number_format((float) $detalle->iva_monto, 2) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #0f172a;">
                        {{ $simboloMoneda }} {{ number_format((float) $detalle->total, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-row">No hay líneas registradas en esta factura.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Resumen y Totales --}}
    <table class="totales-wrapper">
        <tr>
            {{-- Total en Letras y Notas --}}
            <td style="width: 55%; vertical-align: top; padding-right: 10px;">
                <div class="letras-box">
                    <strong style="color: #711C37; font-size: 7.5pt; text-transform: uppercase;">Valor en Letras:</strong><br>
                    <span style="font-weight: bold; font-size: 8.5pt; color: #0f172a;">{{ $totalEnLetras }}</span>

                    @if($factura->estado === \App\Enums\Facturacion\EstadoFactura::Anulada && $factura->motivo_anulacion)
                        <div style="margin-top: 8px; padding-top: 6px; border-top: 1px dashed #cbd5e1; color: #b91c1c;">
                            <strong>Motivo de Anulación:</strong> {{ $factura->motivo_anulacion }}
                        </div>
                    @endif

                    @if(!empty($barcodeBase64))
                        <div style="margin-top: 10px; text-align: left;">
                            <img src="{{ $barcodeBase64 }}" style="height: 38px;" alt="Código de barras">
                        </div>
                    @endif
                </div>
            </td>

            {{-- Cuadro de Totales con estilo oficial #711C37 --}}
            <td style="width: 45%; vertical-align: top;">
                <div class="totales-box">
                    <table class="totales-table">
                        <tr>
                            <td style="text-align: left;">Subtotal Gravado:</td>
                            <td style="text-align: right;">{{ $simboloMoneda }} {{ number_format((float) $factura->subtotal, 2) }}</td>
                        </tr>
                        @if((float) $factura->descuento_total > 0)
                            <tr>
                                <td style="text-align: left; opacity: 0.9;">Descuentos:</td>
                                <td style="text-align: right; opacity: 0.9;">- {{ $simboloMoneda }} {{ number_format((float) $factura->descuento_total, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td style="text-align: left;">IVA (15%):</td>
                            <td style="text-align: right;">{{ $simboloMoneda }} {{ number_format((float) $factura->iva_total, 2) }}</td>
                        </tr>
                        @if((float) $factura->servicio_total > 0)
                            <tr>
                                <td style="text-align: left;">Servicio:</td>
                                <td style="text-align: right;">{{ $simboloMoneda }} {{ number_format((float) $factura->servicio_total, 2) }}</td>
                            </tr>
                        @endif
                        @if((float) $factura->propina_total > 0)
                            <tr>
                                <td style="text-align: left;">Propina:</td>
                                <td style="text-align: right;">{{ $simboloMoneda }} {{ number_format((float) $factura->propina_total, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="total-principal">
                            <td style="text-align: left;">TOTAL FACTURA:</td>
                            <td style="text-align: right;">{{ $simboloMoneda }} {{ number_format((float) $factura->total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Pie Legal Fiscal DGI y Distribución de Copias --}}
    <div class="legal-dgi-footer">
        <div>Documento Fiscal emitido conforme a las disposiciones tributarias de la Dirección General de Ingresos (DGI) de la República de Nicaragua.</div>
        <div style="font-weight: bold; margin-top: 3px;">ORIGINAL: CLIENTE &nbsp;&nbsp;—&nbsp;&nbsp; COPIA: CONTABILIDAD</div>
    </div>
@endsection
