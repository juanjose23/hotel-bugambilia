@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('extra-css')
    .report-footer { position: fixed; bottom: -3mm; left: 4mm; right: 4mm; }
    .report-content { padding-bottom: 14mm; }
    .voucher-card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 9px; background: #fff; }
    .voucher-label { color: #711C37; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: .3px; }
    .voucher-value { margin-top: 2px; color: #172033; font-size: 11px; font-weight: bold; }
    .voucher-muted { color: #64748b; font-size: 8px; }
    .financial-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    .financial-table th { background: #711C37; color: #fff; padding: 7px; border: 1px solid #5a1530; font-size: 8px; text-transform: uppercase; }
    .financial-table td { padding: 7px; border: 1px solid #e2e8f0; font-size: 9px; }
    .financial-table tfoot td { background: #f8fafc; font-weight: bold; }
    .financial-table .grand-total td { background: #711C37; color: #fff; font-size: 11px; }

    .badge-check { display: inline-block; width: 11px; height: 11px; line-height: 11px; text-align: center; border-radius: 50%; background: #16a34a; color: #ffffff; font-size: 8px; font-weight: bold; margin-right: 3px; }
    .badge-star { color: #d97706; font-size: 11px; font-weight: bold; margin-right: 2px; }
    .badge-bullet { color: #475569; font-size: 12px; margin-right: 3px; }
    .tag-included { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 7px; font-weight: bold; padding: 1px 5px; border-radius: 3px; margin-right: 3px; text-transform: uppercase; }
    .tag-service { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 7px; font-weight: bold; padding: 1px 5px; border-radius: 3px; margin-right: 3px; text-transform: uppercase; }
    .tag-food { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 7px; font-weight: bold; padding: 1px 5px; border-radius: 3px; margin-right: 3px; text-transform: uppercase; }
    .tag-consumption { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 7px; font-weight: bold; padding: 1px 5px; border-radius: 3px; margin-right: 3px; text-transform: uppercase; }

    .amenities-list { margin-top: 5px; padding-left: 0; list-style: none; }
    .amenities-list li { display: inline-block; background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; font-size: 8.5px; padding: 3px 7px; border-radius: 4px; margin-right: 4px; margin-bottom: 4px; }
@endsection

@section('content')
    @php
        $simbolo = $reserva->moneda?->simbolo ?? 'C$';
        $esRestaurante = $reserva->tipo_reserva === \App\Enums\Reservas\TipoReserva::RESTAURANTE;
        $resumenRestaurante = $reserva->ultimaEntradaBitacora('resumen_restaurante') ?? [];
        $costoMesa = $esRestaurante
            ? (float) ($resumenRestaurante['costo_mesas'] ?? 0)
            : (float) $reserva->subtotal;
        $costoPreorden = $esRestaurante
            ? (float) ($resumenRestaurante['costo_preorden'] ?? collect($detallePreorden)->sum('subtotal'))
            : 0.0;
        $subtotalConceptos = (float) $reserva->subtotal;
        $otrosCargos = max(0, (float) $reserva->total - $subtotalConceptos + (float) $reserva->descuento);
        $pagos = $reserva->cuentas->flatMap->pagos;

        $detallesReservados = $reserva->detalles->loadMissing(['reservable.habitacion', 'reservable.espacio', 'reservable.servicio']);
        $consumosDirectos = $reserva->cuentas->flatMap->detalles->filter(fn ($d) => (int) $d->estado === 1);
        $serviciosIncluidos = $reserva->habitacion?->servicioAsignaciones?->map(fn($sa) => $sa->servicio)->filter() ?? collect();
    @endphp

    <div class="pagina">
        <div class="report-header">
            @include('reports.layout.partials.header', [
                'logo_base64' => $datosHotel['logo_base64'] ?? null,
                'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
            ])
        </div>

        <div class="report-content" style="margin-top: 9px;">
            {{-- Identificación superior y código de barras --}}
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; page-break-inside: avoid;">
                <tr>
                    <td style="width: 48%; vertical-align: middle; padding: 0; border: 0;">
                        <div class="voucher-label">Expediente de Reserva / Comprobante</div>
                        <div style="font-size: 17px; font-weight: bold; color: #711C37; margin-top: 2px;">{{ $reserva->codigo_reserva }}</div>
                        <div class="voucher-muted" style="margin-top: 3px;">Emitido: {{ $fechaEmision }} · {{ $estadoLabel }}</div>
                    </td>
                    <td style="width: 52%; vertical-align: middle; text-align: right; padding: 0; border: 0;">
                        @if (!empty($barcodeBase64))
                            <img src="{{ $barcodeBase64 }}" style="width: 250px; height: 48px; display: inline-block;" alt="Código de barras de la reserva">
                            <div style="width: 250px; margin-left: auto; text-align: center; font-family: monospace; font-size: 8px; letter-spacing: 1px;">{{ $reserva->codigo_reserva }}</div>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Datos esenciales del cliente y viaje --}}
            <table style="width: 100%; border-collapse: separate; border-spacing: 5px; margin: 0 -5px 8px; page-break-inside: avoid;">
                <tr>
                    <td class="voucher-card" style="width: 38%; vertical-align: top;">
                        <div class="voucher-label">Cliente / Titular</div>
                        <div class="voucher-value">{{ $reserva->nombre_cliente }}</div>
                        <div class="voucher-muted">{{ $reserva->telefono_cliente ?? 'Sin teléfono' }}</div>
                        <div class="voucher-muted">{{ $reserva->email_cliente ?? 'Sin correo' }}</div>
                    </td>
                    <td class="voucher-card" style="width: 32%; vertical-align: top;">
                        <div class="voucher-label">Fechas del viaje</div>
                        <div class="voucher-value">Entrada: {{ $reserva->fecha_check_in->format('d/m/Y') }}</div>
                        @if ($esRestaurante)
                            <div class="voucher-muted">{{ $reserva->hora_reserva ?? 'Hora pendiente' }} · {{ $resumenRestaurante['horas'] ?? 1 }} hora(s)</div>
                        @else
                            <div class="voucher-muted">Salida: {{ $reserva->fecha_check_out?->format('d/m/Y') ?? 'N/D' }}</div>
                        @endif
                    </td>
                    <td class="voucher-card" style="width: 30%; vertical-align: top;">
                        <div class="voucher-label">Ocupación & Recurso</div>
                        <div class="voucher-value">{{ $reserva->habitacion?->nombre ?? $reserva->espacio?->nombre ?? 'Habitación asignada' }}</div>
                        <div class="voucher-muted">{{ $reserva->adultos }} adulto(s) @if($reserva->ninos > 0) · {{ $reserva->ninos }} niño(s) @endif</div>
                        @if ($reserva->habitacion?->categoria)
                            <div class="voucher-muted">Categoría: {{ $reserva->habitacion->categoria->nombre }}</div>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Servicios e Instalaciones Incluidos con la Habitación --}}
            @if ($serviciosIncluidos->count() > 0)
                <div class="voucher-card" style="margin-bottom: 10px; page-break-inside: avoid;">
                    <div class="voucher-label"><span class="badge-star">&#9733;</span> Servicios e Instalaciones Incluidos en la Habitación</div>
                    <ul class="amenities-list">
                        @foreach ($serviciosIncluidos as $srv)
                            <li><span class="badge-check">&#10003;</span> <strong>{{ $srv->nombre }}</strong></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Tabla consolidada de servicios reservados e ítems --}}
            <table class="financial-table" style="page-break-inside: avoid;">
                <thead>
                    <tr>
                        <th style="text-align: left;">Recurso / Servicio / Consumo</th>
                        <th style="width: 110px; text-align: center;">Detalle / Agenda</th>
                        <th style="width: 55px; text-align: center;">Cant.</th>
                        <th style="width: 95px; text-align: right;">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($detallesReservados->count() > 0)
                        @foreach ($detallesReservados as $det)
                            @php
                                $nombreRecurso = $det->reservable?->nombre
                                    ?? $det->reservable?->habitacion?->nombre
                                    ?? $det->reservable?->espacio?->nombre
                                    ?? $det->reservable?->servicio?->nombre
                                    ?? 'Servicio agendado';
                                $esPrincipal = $det->parent_id === null;
                            @endphp
                            <tr>
                                <td>
                                    @if ($esPrincipal)
                                        <span class="tag-included">Principal</span>
                                        <span class="badge-star">&#9733;</span>
                                    @else
                                        <span class="tag-service">Agendado</span>
                                        <span class="badge-bullet">&#8226;</span>
                                    @endif
                                    <strong style="color: {{ $esPrincipal ? '#711C37' : '#172033' }};">
                                        {{ $nombreRecurso }}
                                    </strong>
                                    @if ($det->origen !== null)
                                        <span class="voucher-muted">({{ $det->origen->getLabel() }})</span>
                                    @endif
                                    @if (!empty($det->notas))
                                        <div class="voucher-muted">Nota: {{ $det->notas }}</div>
                                    @endif
                                </td>
                                <td style="text-align: center;" class="voucher-muted">
                                    {{ $det->fecha_inicio ? $det->fecha_inicio->format('d/m H:i') : '-' }}
                                    @if ($det->fecha_fin)
                                        - {{ $det->fecha_fin->format('d/m H:i') }}
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ $det->cantidad }}</td>
                                <td style="text-align: right; font-weight: bold;">{{ $simbolo }} {{ number_format((float) $det->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>
                                <span class="tag-included">Principal</span>
                                <strong>{{ $esRestaurante ? 'Reserva de mesa' : 'Reserva de recurso' }}</strong>
                                <div class="voucher-muted">
                                    {{ $reserva->habitacion?->nombre ?? $reserva->espacio?->nombre ?? $reserva->tipo_reserva->getLabel() }}
                                </div>
                            </td>
                            <td style="text-align: center;" class="voucher-muted">{{ $reserva->fecha_check_in->format('d/m/Y') }}</td>
                            <td style="text-align: center;">1</td>
                            <td style="text-align: right; font-weight: bold;">{{ $simbolo }} {{ number_format((float) $reserva->subtotal, 2) }}</td>
                        </tr>
                    @endif

                    @if (! empty($detallePreorden))
                        @foreach ($detallePreorden as $itemPre)
                            <tr>
                                <td style="padding-left: 12px;">
                                    <span class="tag-food">Comida</span>
                                    <strong>{{ $itemPre['nombre'] ?? 'Platillo preordenado' }}</strong>
                                    @if (! empty($itemPre['observaciones']))
                                        <div class="voucher-muted">Nota: {{ $itemPre['observaciones'] }}</div>
                                    @endif
                                </td>
                                <td style="text-align: center;" class="voucher-muted">Preorden</td>
                                <td style="text-align: center;">{{ $itemPre['cantidad'] ?? 1 }}</td>
                                <td style="text-align: right;">{{ $simbolo }} {{ number_format((float) ($itemPre['subtotal'] ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    @endif

                    @if ($consumosDirectos->count() > 0)
                        @foreach ($consumosDirectos as $itemConsumo)
                            @if ($detallesReservados->pluck('id')->doesntContain($itemConsumo->origen_id))
                                <tr>
                                    <td style="padding-left: 12px;">
                                        <span class="tag-consumption">Consumo</span>
                                        <strong>{{ $itemConsumo->concepto }}</strong>
                                        @if (! empty($itemConsumo->descripcion))
                                            <div class="voucher-muted">{{ $itemConsumo->descripcion }}</div>
                                        @endif
                                    </td>
                                    <td style="text-align: center;" class="voucher-muted">Consumo Estancia</td>
                                    <td style="text-align: center;">{{ (int) $itemConsumo->cantidad }}</td>
                                    <td style="text-align: right;">{{ $simbolo }} {{ number_format((float) $itemConsumo->subtotal, 2) }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;">Subtotal de conceptos</td>
                        <td style="text-align: right;">{{ $simbolo }} {{ number_format((float) $reserva->subtotal, 2) }}</td>
                    </tr>
                    @if ((float) $reserva->descuento > 0)
                        <tr>
                            <td colspan="3" style="text-align: right;">Descuento aplicable</td>
                            <td style="text-align: right;">- {{ $simbolo }} {{ number_format((float) $reserva->descuento, 2) }}</td>
                        </tr>
                    @endif
                    @if ($otrosCargos > 0)
                        <tr>
                            <td colspan="3" style="text-align: right;">Impuestos y cargos del servicio</td>
                            <td style="text-align: right;">{{ $simbolo }} {{ number_format($otrosCargos, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total">
                        <td colspan="3" style="text-align: right;">Total del expediente de reserva</td>
                        <td style="text-align: right;">{{ $simbolo }} {{ number_format((float) $reserva->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            {{-- Estado del pago y verificación --}}
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: avoid;">
                <tr>
                    <td style="width: 68%; vertical-align: top; padding-right: 12px; border: 0;">
                        <div class="voucher-card">
                            <div class="voucher-label">Estado financiero de la cuenta</div>
                            <div style="margin-top: 5px; font-size: 10px;">
                                Total Pagado: <strong>{{ $simbolo }} {{ number_format((float) $reserva->total_pagado, 2) }}</strong>
                                &nbsp; · &nbsp;
                                Saldo Pendiente: <strong style="color: #711C37;">{{ $simbolo }} {{ number_format((float) $reserva->saldo, 2) }}</strong>
                            </div>
                            <div class="voucher-muted" style="margin-top: 4px;">
                                {{ $pagos->isEmpty() ? 'No se registran abonados previos.' : $pagos->count().' pago(s) o abono(s) registrado(s).' }}
                            </div>
                        </div>
                        <div style="margin-top: 8px; border-left: 3px solid #711C37; padding: 6px 8px; background: #f8fafc; color: #475569; font-size: 8px; line-height: 1.45;">
                            Presente este expediente al ingresar al hotel. Los servicios agendados (sauna, spa, restaurante) están reservados a las horas indicadas.
                        </div>
                    </td>
                    <td style="width: 32%; text-align: center; vertical-align: top; border: 0;">
                        @if (!empty($qrCodeBase64))
                            <img src="{{ $qrCodeBase64 }}" style="width: 88px; height: 88px; display: block; margin: 0 auto 3px;" alt="Código QR de verificación">
                            <div class="voucher-label">Verificación QR</div>
                            <div class="voucher-muted" style="font-family: monospace;">{{ substr($tokenQr, 0, 16) }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        @include('reports.layout.partials.footer', [
            'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
            'usuario' => auth()->user()?->name ?? 'Sistema',
            'generadoEn' => $fechaEmision,
        ])
    </div>
@endsection
