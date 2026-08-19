@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Solicitud de Compra',
    'codigo' => $codigoReporte ?? 'HTB-COM-010',
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
                    <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Solicitante</strong><br>
                    <span style="font-size: 11pt; font-weight: bold;">{{ $solicitud->colaborador?->codigo }} - {{ $solicitud->colaborador?->persona?->nombre_completo }}</span><br>
                    <span style="font-size: 8.5pt; color: #64748b;">Departamento: {{ $solicitud->departamentoSolicitante?->nombre }}</span>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Referencia</strong><br>
                    <span style="font-size: 11pt; font-weight: bold; color: #711C37;">{{ $solicitud->codigo }}</span><br>
                    <span style="font-size: 8.5pt; color: #64748b;">Fecha: {{ $solicitud->fecha_solicitud?->format('d/m/Y') }}</span><br>
                    <span style="font-size: 8.5pt; color: #64748b;">Necesita: {{ $solicitud->fecha_necesita?->format('d/m/Y') ?: 'No definida' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 4px; margin-bottom: 16px;">
        <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Estado:</strong>
        <strong style="font-size: 8.5pt;">{{ $estadoLabel }}</strong>
    </div>

    @if ($solicitud->motivo)
        <div style="margin-bottom: 16px;">
            <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Motivo:</strong><br>
            <span style="font-size: 8.5pt;">{{ $solicitud->motivo }}</span>
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:40px; text-align: center;">#</th>
                <th>Producto</th>
                <th style="width:100px; text-align: center;">Variante</th>
                <th style="width:100px; text-align: center;">Cant. Solicitada</th>
                <th style="width:100px; text-align: center;">Cant. Aprobada</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($solicitud->items as $item)
                <tr>
                    <td style="text-align:center;">{{ $loop->iteration }}</td>
                    <td><strong>{{ $item->producto?->nombre }}</strong></td>
                    <td style="text-align:center;">{{ $item->productoVariante?->codigo ?: '—' }}</td>
                    <td style="text-align:center;">{{ number_format((float)$item->cantidad_solicitada, 2) }}</td>
                    <td style="text-align:center;">{{ $item->cantidad_aprobada > 0 ? number_format((float)$item->cantidad_aprobada, 2) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($solicitud->notas)
        <div style="margin-top: 16px; padding: 8px 12px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 4px;" class="avoid-break">
            <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Notas:</strong><br>
            <span style="font-size: 8.5pt;">{{ $solicitud->notas }}</span>
        </div>
    @endif

    <div style="margin-top: 35px;" class="avoid-break">
        <table style="width: 100%;">
            <tr>
                <td style="width: 45%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Solicitante</strong>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Vo.Bo. Compras</strong>
                </td>
            </tr>
        </table>
    </div>
@endsection
