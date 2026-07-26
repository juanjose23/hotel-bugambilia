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

        <div class="report-content" style="margin-top: 10px;">
            <div style="text-align: center; margin-bottom: 12px;">
                <span style="font-size: 14px; font-weight: bold; color: #711C37; text-transform: uppercase;">Comanda Restaurante</span>
            </div>

            <div style="margin-bottom: 15px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <strong style="color: #711C37; font-size: 10px; text-transform: uppercase;">Datos del Pedido</strong><br>
                            <span style="font-size: 12px; font-weight: bold;">{{ $pedido->codigo }}</span><br>
                            <span style="font-size: 10px; color: #4a5568;">Mesa / Ubic.: <strong>{{ $pedido->mesa->nombre ?? 'Llevar / Habitación' }}</strong></span><br>
                            <span style="font-size: 10px; color: #4a5568;">Fecha Hora: {{ $pedido->created_at?->format('d/m/Y H:i:s') }}</span><br>
                            <span style="font-size: 10px; color: #4a5568;">Cliente: {{ $clienteNombre }}</span>
                        </td>
                        <td style="width: 50%; vertical-align: top; text-align: right;">
                            @if($pedido->mesero?->persona)
                                <strong style="color: #711C37; font-size: 10px; text-transform: uppercase;">Atendido por</strong><br>
                                <span style="font-size: 11px;">Mesero: {{ $pedido->mesero->persona->nombre_completo }}</span><br>
                            @endif
                            @if($habitacionNumero)
                                <span style="font-size: 10px; color: #4a5568;">Habitación: {{ $habitacionNumero }}</span><br>
                            @endif
                            <span style="font-size: 10px; color: #4a5568;">Emisión: {{ $fechaEmision }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center; border-bottom: 1px solid #000; font-weight: bold; text-transform: uppercase; font-size: 11px;">Cant</th>
                        <th style="text-align: left; border-bottom: 1px solid #000; font-weight: bold; text-transform: uppercase; font-size: 11px;">Descripción / Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td style="text-align: center; padding: 5px 0; border-bottom: 1px dotted #aaa; font-weight: bold; font-size: 14px;">x{{ (int) $item->cantidad }}</td>
                            <td style="padding: 5px 0; border-bottom: 1px dotted #aaa;">
                                <strong style="font-size: 13px;">{{ $item->plato->nombre ?? 'Platillo' }}</strong>
                                @if($item->observaciones)
                                    <br><span style="font-size: 11px; font-weight: bold; color: #111;">-> OBS: {{ $item->observaciones }}</span>
                                @elseif($item->notas)
                                    <br><span style="font-size: 11px; font-weight: bold; color: #111;">-> NOTA: {{ $item->notas }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 8px;">Sin ítems para esta área.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($pedido->notas)
                <div style="border: 1px solid #000; padding: 5px; margin-top: 8px; margin-bottom: 12px; font-size: 11px;">
                    <strong>OBSERVACIONES GENERALES:</strong><br>
                    {{ $pedido->notas }}
                </div>
            @endif

            <div style="border-top: 2px solid #000; padding-top: 6px; margin-top: 8px; text-align: right;">
                <span style="font-size: 14px; font-weight: bold;">TOTAL PEDIDO: {{ $simboloMoneda }} {{ number_format((float) $pedido->total, 2) }}</span>
            </div>
        </div>

        @include('reports.layout.partials.footer', [
            'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
        ])
    </div>
@endsection
