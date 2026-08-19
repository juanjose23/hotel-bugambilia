@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Devolución de Compra',
    'codigo' => $codigoReporte ?? 'HTB-COM-016',
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
                    <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Orden de Compra / Proveedor</strong><br>
                    <span style="font-size: 11pt; font-weight: bold;">{{ $record->ordenCompraCodigo ?? 'N/A' }}</span><br>
                    @if($record->recepcionCompraCodigo)
                        <span style="font-size: 8pt; color: #64748b;">Recepción Ref: {{ $record->recepcionCompraCodigo }}</span>
                    @endif
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Código Devolución</strong><br>
                    <span style="font-size: 11pt; font-weight: bold; color: #711C37;">{{ $record->codigo }}</span><br>
                    <span style="font-size: 8pt; color: #64748b;">Fecha: {{ $record->fecha_devolucion?->format('d/m/Y') }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 4px; margin-bottom: 16px;">
        <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Estado:</strong>
        <strong style="font-size: 8.5pt;">{{ $record->estado?->label() ?? 'N/A' }}</strong>
        @if($record->ordenCompraCodigo)
            <span style="font-size: 8pt; color: #64748b; margin-left: 16px;">OC: {{ $record->ordenCompraCodigo }}</span>
        @endif
        @if($record->documento_externo)
            <span style="font-size: 8pt; color: #64748b; margin-left: 16px;">Doc. Externo: {{ $record->documento_externo }}</span>
        @endif
    </div>

    @if($record->motivo)
        <div style="background: #fef3c7; border: 1px solid #fde68a; padding: 8px 12px; border-radius: 4px; margin-bottom: 16px;">
            <strong style="color: #92400e; font-size: 8pt; text-transform: uppercase;">Motivo de Devolución:</strong><br>
            <span style="font-size: 8.5pt; color: #78350f;">{{ $record->motivo }}</span>
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:40px; text-align: center;">#</th>
                <th>Producto</th>
                <th style="width:100px; text-align: center;">Variante</th>
                <th style="width:90px; text-align: center;">U. Medida</th>
                <th style="width:90px; text-align: center;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($record->items as $item)
                <tr>
                    <td style="text-align:center;">{{ $loop->iteration }}</td>
                    <td><strong>{{ $item->producto?->nombre }}</strong></td>
                    <td style="text-align:center;">{{ $item->variante?->codigo ?: '—' }}</td>
                    <td style="text-align:center;">{{ $item->unidadMedida?->valor ?: '—' }}</td>
                    <td style="text-align:center; font-weight: bold;">{{ number_format($item->cantidad_devolver, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 35px;" class="avoid-break">
        <table style="width: 100%;">
            <tr>
                <td style="width: 30%; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                    <strong style="font-size: 8pt; text-transform: uppercase; color: #64748b;">Responsable</strong>
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
