@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('content')
    <div class="pagina">
        <div class="report-content" style="margin-top: 0;">
            <div style="text-align: center; margin-bottom: 12px;">
                <span style="font-size: 14px; font-weight: bold; color: #065f46; text-transform: uppercase;">Comprobante de Pago</span>
            </div>

            <div style="margin-bottom: 15px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <strong style="color: #711C37; font-size: 10px; text-transform: uppercase;">Datos de la Cuenta</strong><br>
                            <span style="font-size: 12px; font-weight: bold;">{{ $cuenta->numero_cuenta }}</span><br>
                            <span style="font-size: 10px; color: #4a5568;">Tipo: {{ $cuenta->tipo_cuenta->getLabel() }}</span><br>
                            <span style="font-size: 10px; color: #4a5568;">Cliente: {{ $cuenta->cliente?->nombre_completo ?? 'Cliente General' }}</span><br>
                            <span style="font-size: 10px; color: #4a5568;">Estado: <strong style="color: #065f46;">{{ $cuenta->estado->getLabel() }}</strong></span>
                        </td>
                        <td style="width: 50%; vertical-align: top; text-align: right;">
                            <strong style="color: #065f46; font-size: 10px; text-transform: uppercase;">Datos del Pago</strong><br>
                            @if($pago)
                                <span style="font-size: 11px;">Método: <strong>{{ $pago->forma_pago->getLabel() }}</strong></span><br>
                                @if($pago->referencia_transaccion)
                                    <span style="font-size: 10px; color: #4a5568;">Ref: {{ $pago->referencia_transaccion }}</span><br>
                                @endif
                            @endif
                            <span style="font-size: 10px; color: #4a5568;">Emisión: {{ $fechaEmision }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="border: 2px solid #065f46; padding: 12px; border-radius: 6px; background: #f0fdf4; margin-bottom: 15px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; padding: 4px 0;">
                            <strong style="font-size: 10px; color: #065f46; text-transform: uppercase;">Total de la Cuenta</strong><br>
                            <span style="font-size: 16px; font-weight: bold; color: #065f46;">{{ $simboloMoneda }} {{ number_format($totalCuenta, 2) }}</span>
                        </td>
                        <td style="width: 25%; padding: 4px 0; text-align: center;">
                            <strong style="font-size: 10px; color: #4a5568; text-transform: uppercase;">Monto Recibido</strong><br>
                            <span style="font-size: 14px; font-weight: bold;">{{ $simboloMoneda }} {{ number_format($montoPagado, 2) }}</span>
                        </td>
                        <td style="width: 25%; padding: 4px 0; text-align: right;">
                            @if($vuelto > 0)
                                <strong style="font-size: 10px; color: #dc2626; text-transform: uppercase;">Vuelto</strong><br>
                                <span style="font-size: 14px; font-weight: bold; color: #dc2626;">{{ $simboloMoneda }} {{ number_format($vuelto, 2) }}</span>
                            @else
                                <strong style="font-size: 10px; color: #065f46; text-transform: uppercase;">Pago Completo</strong><br>
                                <span style="font-size: 12px; color: #065f46; font-weight: bold;">EXACTO</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin: 15px 0;">
                <div style="display: inline-block; border: 2px solid #065f46; padding: 8px 24px; border-radius: 6px; background: #d1fae5;">
                    <span style="font-size: 14px; font-weight: bold; color: #065f46; text-transform: uppercase; letter-spacing: 1px;">PAGO CONFIRMADO</span>
                </div>
            </div>
        </div>
    </div>
@endsection
