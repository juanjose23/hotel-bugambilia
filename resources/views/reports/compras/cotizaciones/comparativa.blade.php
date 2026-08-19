@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Cuadro Comparativo de Cotizaciones',
    'codigo' => $codigoReporte ?? 'HTB-COM-009-C',
    'orientation' => 'landscape',
])

@section('content')
    @if(!empty($barcodeBase64))
        <div style="text-align: right; margin-bottom: 8px;">
            <img src="{{ $barcodeBase64 }}" style="height: 45px;" alt="Código de barras">
        </div>
    @endif

    <div style="margin-bottom: 16px;">
        <table style="width: 100%; font-size: 8.5pt;">
            <tr>
                <td>
                    <strong style="color: #711C37;">Departamento:</strong> {{ $record->departamentoSolicitante?->nombre }}<br>
                    <strong style="color: #711C37;">Solicitante:</strong> {{ $record->colaborador?->persona?->nombre_completo }}
                </td>
                <td style="text-align: right;">
                    <strong style="color: #711C37;">Fecha Solicitud:</strong> {{ $record->fecha_solicitud?->format('d/m/Y') }}<br>
                    <strong style="color: #711C37;">Estado:</strong> {{ $record->estado?->label() }}
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table" style="font-size: 8pt;">
        <thead>
            <tr>
                <th style="width: 180px;">Producto / Descripción</th>
                @foreach($record->cotizaciones as $cot)
                    <th style="text-align: center;">
                        {{ $cot->proveedor?->persona?->personaJuridica?->razon_social ?? $cot->proveedor?->contacto_nombre }}<br>
                        <small style="font-weight: normal; font-size: 7.5pt;">{{ $cot->moneda?->codigo ?? 'USD' }}</small>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($record->items as $sItem)
                <tr>
                    <td>
                        <strong>{{ $sItem->producto?->nombre }}</strong><br>
                        <span style="color: #64748b; font-size: 7.5pt;">{{ $sItem->variante?->nombre_variante ?? 'Estándar' }}</span><br>
                        <span style="color: #711C37; font-weight: bold;">Cant: {{ number_format((float)($sItem->cantidad_aprobada > 0 ? $sItem->cantidad_aprobada : $sItem->cantidad_solicitada), 0) }}</span>
                    </td>
                    @foreach($record->cotizaciones as $cot)
                        @php
                            $cItem = $cot->items->where('producto_id', $sItem->producto_id)->first();
                            $isWinner = $cItem?->es_elegido ?? false;
                        @endphp
                        <td style="text-align: center; @if($isWinner) background-color: #dcfce7; @endif">
                            @if($cItem)
                                <span style="font-size: 8.5pt; font-weight: bold; @if($isWinner) color: #166534; @endif">
                                    {{ $cot->moneda?->simbolo ?? '$' }}{{ number_format((float)$cItem->precio_unitario, 2) }}
                                </span>
                                <br>
                                <span style="font-size: 7.5pt; color: #64748b;">
                                    {{ $cItem->variante?->nombre_variante ?? 'N/A' }}
                                </span>
                                @if($isWinner)
                                    <div style="font-size: 7pt; color: #166534; font-weight: bold; margin-top: 2px;">ADJUDICADO</div>
                                @endif
                            @else
                                <span style="color: #94a3b8; font-style: italic;">No Cotizado</span>
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
                    <td style="text-align: center; font-weight: bold; font-size: 8.5pt; color: #711C37;">
                        {{ $cot->moneda?->simbolo ?? '$' }}{{ number_format((float)$cot->total, 2) }}
                    </td>
                @endforeach
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px;" class="avoid-break">
        <table style="width: 100%;">
            <tr>
                <td style="width: 30%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Revisado por</strong>
                </td>
                <td style="width: 5%;"></td>
                <td style="width: 30%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Gerencia Administrativa</strong>
                </td>
                <td style="width: 5%;"></td>
                <td style="width: 30%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Contabilidad / Auditoría</strong>
                </td>
            </tr>
        </table>
    </div>
@endsection
