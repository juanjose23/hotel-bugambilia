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
            <!-- Info Solicitud -->
            <div style="margin-bottom: 15px;">
                <table style="width: 100%; font-size: 10px;">
                    <tr>
                        <td>
                            <strong style="color: #711C37;">Departamento:</strong> {{ $record->departamentoSolicitante->nombre }}<br>
                            <strong style="color: #711C37;">Solicitante:</strong> {{ $record->colaborador->persona->nombre_completo }}
                        </td>
                        <td style="text-align: right;">
                            <strong style="color: #711C37;">Fecha Solicitud:</strong> {{ $record->fecha_solicitud->format('d/m/Y') }}<br>
                            <strong style="color: #711C37;">Estado:</strong> {{ $record->estado->label() }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Cuadro Comparativo -->
            <table class="data-table" style="font-size: 8px;">
                <thead>
                    <tr>
                        <th style="width: 150px;">Producto / Descripción</th>
                        @foreach($record->cotizaciones as $cot)
                            <th style="text-align: center;">
                                {{ $cot->proveedor->persona->personaJuridica->razon_social ?? $cot->proveedor->contacto_nombre }}<br>
                                <small style="font-weight: normal; font-size: 7px;">{{ $cot->moneda?->codigo ?? 'USD' }}</small>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($record->items as $sItem)
                        <tr>
                            <td>
                                <strong>{{ $sItem->producto->nombre }}</strong><br>
                                <span style="color: #666; font-size: 7px;">{{ $sItem->variante->nombre_variante ?? 'Estándar' }}</span><br>
                                <span style="color: #711C37; font-weight: bold;">Cant: {{ number_format($sItem->cantidad_aprobada > 0 ? $sItem->cantidad_aprobada : $sItem->cantidad_solicitada, 0) }}</span>
                            </td>
                            @foreach($record->cotizaciones as $cot)
                                @php
                                    $cItem = $cot->items->where('producto_id', $sItem->producto_id)->first();
                                    $isWinner = $cItem?->es_elegido ?? false;
                                @endphp
                                <td style="text-align: center; @if($isWinner) background-color: #dcfce7; @endif">
                                    @if($cItem)
                                        <span style="font-size: 9px; font-weight: bold; @if($isWinner) color: #166534; @endif">
                                            {{ $cot->moneda?->simbolo ?? '$' }}{{ number_format($cItem->precio_unitario, 2) }}
                                        </span>
                                        <br>
                                        <span style="font-size: 7px; color: #666;">
                                            {{ $cItem->variante->nombre_variante ?? 'N/A' }}
                                        </span>
                                        @if($isWinner)
                                            <div style="font-size: 7px; color: #166534; font-weight: bold; margin-top: 2px;">★ ADJUDICADO</div>
                                        @endif
                                    @else
                                        <span style="color: #ccc; font-style: italic;">No Cotizado</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8fafc;">
                        <td style="text-align: right; font-weight: bold; color: #711C37;">TOTALES ORIGINALES</td>
                        @foreach($record->cotizaciones as $cot)
                            <td style="text-align: center; font-weight: bold; font-size: 9px; color: #711C37;">
                                {{ $cot->moneda?->simbolo ?? '$' }}{{ number_format($cot->total, 2) }}
                            </td>
                        @endforeach
                    </tr>
                    <tr style="background-color: #fdf2f8;">
                        <td style="text-align: right; font-weight: bold; color: #711C37; font-size: 8px;">COMPARATIVA EN USD ($)</td>
                        @foreach($record->cotizaciones as $cot)
                            @php
                                $totalUSD = $cot->moneda?->codigo === 'USD'
                                    ? $cot->total
                                    : ($cot->tasa_cambio > 1 ? $cot->total / $cot->tasa_cambio : $cot->total / 36.52);
                            @endphp
                            <td style="text-align: center; font-weight: bold; font-size: 9px; color: #166534;">
                                ${{ number_format($totalUSD, 2) }}
                                @if($cot->moneda?->codigo !== 'USD')
                                    <br><small style="font-size: 6px; font-weight: normal; color: #666;">@ {{ number_format($cot->tasa_cambio ?: 36.52, 4) }}</small>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr style="background-color: #f0fdf4;">
                        <td style="text-align: right; font-weight: bold; color: #711C37; font-size: 8px;">COMPARATIVA EN NIO (C$)</td>
                        @foreach($record->cotizaciones as $cot)
                            @php
                                $totalNIO = $cot->moneda?->codigo === 'NIO'
                                    ? $cot->total
                                    : ($cot->tasa_cambio > 1 ? $cot->total * $cot->tasa_cambio : $cot->total * 36.52);
                            @endphp
                            <td style="text-align: center; font-weight: bold; font-size: 9px; color: #1e3a8a;">
                                C${{ number_format($totalNIO, 2) }}
                                @if($cot->moneda?->codigo !== 'NIO')
                                    <br><small style="font-size: 6px; font-weight: normal; color: #666;">@ {{ number_format($cot->tasa_cambio ?: 36.52, 4) }}</small>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>

            <!-- Resumen de Adjudicación Agrupado por Proveedor -->
            @php
                $adjudicaciones = [];
                foreach($record->cotizaciones as $cot) {
                    $monto = $cot->items->where('es_elegido', true)->sum('subtotal');
                    if($monto > 0) {
                        $adjudicaciones[] = [
                            'proveedor' => $cot->proveedor->persona->personaJuridica->razon_social ?? $cot->proveedor->contacto_nombre,
                            'monto' => $monto,
                            'moneda' => $cot->moneda?->simbolo ?? '$',
                            'codigo_moneda' => $cot->moneda?->codigo ?? 'USD',
                            'tasa_cambio' => $cot->tasa_cambio ?: 36.52
                        ];
                    }
                }

                $totalGralUSD = collect($adjudicaciones)->sum(function($adj) {
                    return $adj['codigo_moneda'] === 'USD'
                        ? $adj['monto']
                        : $adj['monto'] / $adj['tasa_cambio'];
                });

                $totalGralNIO = collect($adjudicaciones)->sum(function($adj) {
                    return $adj['codigo_moneda'] === 'NIO'
                        ? $adj['monto']
                        : $adj['monto'] * $adj['tasa_cambio'];
                });
            @endphp

            @if(count($adjudicaciones) > 0)
                <div style="margin-top: 25px; border: 1px solid #711C37; border-radius: 8px; overflow: hidden;">
                    <div style="background-color: #711C37; color: #fff; padding: 6px 12px; font-weight: bold; font-size: 10px; text-transform: uppercase;">
                        Resumen de Adjudicación por Proveedor
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #fdf2f8;">
                                <th style="padding: 6px 12px; text-align: left; font-size: 8px; border-bottom: 1px solid #711C37;">Proveedor Adjudicado</th>
                                <th style="padding: 6px 12px; text-align: right; font-size: 8px; border-bottom: 1px solid #711C37;">Monto Adjudicado (Neto)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adjudicaciones as $adj)
                                <tr>
                                    <td style="padding: 6px 12px; font-size: 9px; border-bottom: 1px solid #f1f5f9;">
                                        <strong>{{ $adj['proveedor'] }}</strong>
                                    </td>
                                    <td style="padding: 6px 12px; font-size: 10px; text-align: right; font-weight: bold; color: #711C37; border-bottom: 1px solid #f1f5f9;">
                                        {{ $adj['moneda'] }}{{ number_format($adj['monto'], 2) }}
                                        @if($adj['codigo_moneda'] === 'NIO')
                                            <br><small style="font-weight: normal; color: #666; font-size: 7px;">(Equiv: ${{ number_format($adj['monto'] / $adj['tasa_cambio'], 2) }} USD)</small>
                                        @elseif($adj['codigo_moneda'] === 'USD')
                                            <br><small style="font-weight: normal; color: #666; font-size: 7px;">(Equiv: C${{ number_format($adj['monto'] * $adj['tasa_cambio'], 2) }} NIO)</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #711C37;">
                                <td style="padding: 8px 12px; color: #fff; font-weight: bold; font-size: 10px; text-align: right;">TOTAL GENERAL ADJUDICADO (USD)</td>
                                <td style="padding: 8px 12px; color: #fff; font-weight: bold; font-size: 12px; text-align: right;">
                                    ${{ number_format($totalGralUSD, 2) }} USD
                                </td>
                            </tr>
                            <tr style="background-color: #1e3a8a;">
                                <td style="padding: 8px 12px; color: #fff; font-weight: bold; font-size: 10px; text-align: right;">TOTAL GENERAL ADJUDICADO (NIO)</td>
                                <td style="padding: 8px 12px; color: #fff; font-weight: bold; font-size: 12px; text-align: right;">
                                    C${{ number_format($totalGralNIO, 2) }} NIO
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif

            <!-- Firmas -->
            <div style="margin-top: 30px;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 30%; text-align: center; border-top: 1px solid #ccc; padding-top: 5px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Revisado por</strong>
                        </td>
                        <td style="width: 5%;"></td>
                        <td style="width: 30%; text-align: center; border-top: 1px solid #ccc; padding-top: 5px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Gerencia Administrativa</strong>
                        </td>
                        <td style="width: 5%;"></td>
                        <td style="width: 30%; text-align: center; border-top: 1px solid #ccc; padding-top: 5px;">
                            <strong style="font-size: 10px; text-transform: uppercase; color: #999;">Contabilidad / Auditoría</strong>
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
