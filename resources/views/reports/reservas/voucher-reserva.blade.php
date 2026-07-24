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

            {{-- Bloque Información Principal del Huésped y la Reserva --}}
            <div style="margin-bottom: 15px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <strong style="color: #711C37; font-size: 10px; text-transform: uppercase;">Cliente / Titular</strong><br>
                            <span style="font-size: 12px; font-weight: bold;">{{ $reserva->nombre_cliente }}</span><br>
                            <span style="font-size: 10px; color: #4a5568;">Email: {{ $reserva->email_cliente ?? 'N/D' }}</span><br>
                            <span style="font-size: 10px; color: #4a5568;">Teléfono: {{ $reserva->telefono_cliente ?? 'N/D' }}</span>
                        </td>
                        <td style="width: 50%; vertical-align: top; text-align: right;">
                            <strong style="color: #711C37; font-size: 10px; text-transform: uppercase;">Voucher de Reserva</strong><br>
                            <span style="font-size: 13px; font-weight: bold; color: #711C37;">{{ $reserva->codigo_reserva }}</span><br>
                            <span style="font-size: 10px; color: #4a5568;">Emisión: {{ $fechaEmision }}</span><br>
                            <span style="font-size: 10px; color: #4a5568;">Estado: <strong>{{ $estadoLabel }}</strong></span>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Resumen de Ocupación y Fechas --}}
            <div style="background: #f8fafc; border: 1px solid #711C37; padding: 8px; border-radius: 4px; margin-bottom: 15px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 33%;">
                            <strong style="color: #711C37; font-size: 9px; text-transform: uppercase;">Check-In Esperado</strong><br>
                            <span style="font-size: 11px; font-weight: bold;">{{ $reserva->fecha_check_in->format('d/m/Y') }}</span>
                        </td>
                        <td style="width: 33%;">
                            <strong style="color: #711C37; font-size: 9px; text-transform: uppercase;">Check-Out Esperado</strong><br>
                            <span style="font-size: 11px; font-weight: bold;">{{ $reserva->fecha_check_out?->format('d/m/Y') ?? 'N/D' }}</span>
                        </td>
                        <td style="width: 34%; text-align: right;">
                            <strong style="color: #711C37; font-size: 9px; text-transform: uppercase;">Alojamiento / Recurso</strong><br>
                            <span style="font-size: 11px; font-weight: bold; color: #711C37;">
                                {{ $reserva->habitacion->nombre ?? $reserva->espacio->nombre ?? 'Por asignar' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Desglose de Ocupantes y Montos --}}
            <table class="data-table" style="margin-bottom: 15px;">
                <thead>
                    <tr>
                        <th>Concepto / Tipo Reserva</th>
                        <th style="width: 80px; text-align: center;">Adultos</th>
                        <th style="width: 80px; text-align: center;">Niños</th>
                        <th style="width: 110px; text-align: center;">Cuenta Abierta</th>
                        <th style="width: 100px; text-align: right;">Total Reserva</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ is_object($reserva->tipo_reserva) && method_exists($reserva->tipo_reserva, 'label') ? $reserva->tipo_reserva->label() : (string)$reserva->tipo_reserva }}</strong>
                            <br><span style="font-size: 8px; color: #64748b;">{{ $reserva->detalles_count ?? 1 }} recurso(s) contratado(s)</span>
                        </td>
                        <td style="text-align: center;">{{ $reserva->adultos }}</td>
                        <td style="text-align: center;">{{ $reserva->ninos }}</td>
                        <td style="text-align: center;">
                            <span class="badge {{ $reserva->solicita_cuenta ? 'badge-on' : '' }}">
                                {{ $reserva->solicita_cuenta ? 'SOLICITADA' : 'NO SOLICITADA' }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: bold; font-size: 11px; color: #711C37;">
                            C$ {{ number_format((float)$reserva->total, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Listado de Huéspedes Registrados --}}
            @if ($reserva->huespedes->isNotEmpty())
                <div style="margin-bottom: 15px;">
                    <strong style="color: #711C37; font-size: 10px; text-transform: uppercase; display: block; margin-bottom: 4px;">
                        Huéspedes Acompañantes Registrados
                    </strong>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 30px; text-align: center;">#</th>
                                <th>Nombre Completo</th>
                                <th style="width: 120px; text-align: center;">Identificación</th>
                                <th style="width: 100px; text-align: center;">Tipo Huésped</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reserva->huespedes as $index => $huesped)
                                <tr>
                                    <td style="text-align: center;">{{ $index + 1 }}</td>
                                    <td>{{ $huesped->nombre }}</td>
                                    <td style="text-align: center;">{{ $huesped->identificacion ?? '—' }}</td>
                                    <td style="text-align: center;">
                                        {{ is_object($huesped->tipo_huesped) && method_exists($huesped->tipo_huesped, 'label') ? $huesped->tipo_huesped->label() : (string)$huesped->tipo_huesped }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- BLOQUE CÓDIGO QR DE VERIFICACIÓN CENTRADO EN MEDIO --}}
            @if(!empty($qrCodeBase64))
                <div style="text-align: center; margin: 18px 0 15px 0;">
                    <div style="display: inline-block; border: 1.5px solid #711C37; padding: 8px 18px; border-radius: 8px; background: #ffffff; text-align: center;">
                        <img src="{{ $qrCodeBase64 }}" style="width: 105px; height: 105px; display: block; margin: 0 auto 4px auto;" alt="Código QR de Verificación">
                        <div style="font-size: 9px; font-weight: bold; color: #711C37; text-transform: uppercase; letter-spacing: 0.5px;">CÓDIGO QR DE VERIFICACIÓN</div>
                        <div style="font-size: 8px; font-family: monospace; color: #4a5568; margin-top: 2px;">{{ $reserva->codigo_reserva }}</div>
                    </div>
                </div>
            @endif

            {{-- Políticas e Instrucciones de Recepción --}}
            <div style="border: 1px solid #e2e8f0; padding: 8px; border-radius: 4px; font-size: 8px; color: #4a5568; background: #fafafa;">
                <strong style="color: #711C37; font-size: 9px; text-transform: uppercase;">Políticas e Instrucciones de Recepción:</strong>
                <ul style="margin-top: 3px; padding-left: 12px;">
                    <li>Check-In disponible a partir de las 14:00 hrs. Presentar documento de identidad original al ingresar.</li>
                    <li>Este voucher sirve como comprobante oficial de su reservación en Hotel Bugambilias. Escanee el código QR en recepción para auto check-in.</li>
                    <li>Token QR de Validación: <span style="font-family: monospace; font-weight: bold; color: #711C37;">{{ substr($tokenQr, 0, 16) }}</span></li>
                </ul>
            </div>

        </div>

        @include('reports.layout.partials.footer', [
            'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
        ])
    </div>
@endsection
