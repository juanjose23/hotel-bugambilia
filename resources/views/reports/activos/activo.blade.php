@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-002')
@section('report_name', 'Ficha Técnica de Activo Fijo')

@section('content')
<div class="report-page">
    <table class="page-frame">
        <tbody>
            <tr>
                <td class="frame-body" style="padding: 40px;">
                    <!-- Encabezado -->
                    <div class="doc-header">
                        <table>
                            <tr>
                                <td style="width:35%;">
                                    @if(!empty($logo_base64))
                                        <img src="{{ $logo_base64 }}" class="hdr-logo">
                                    @else
                                        <div class="hdr-title">Hotel Bugambilias</div>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <div class="hdr-title" style="font-size: 14px;">Ficha Técnica del Activo</div>
                                    <div class="hdr-code">HTB-ACT-002</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Ficha Detallada del Activo -->
                    <div style="margin-bottom: 25px; background: #fff; border: 1px solid #711C37; border-radius: 4px; overflow: hidden;">
                        <div style="background: #711C37; padding: 10px 15px; color: #fff; font-weight: bold; font-size: 11px;">
                            IDENTIFICACIÓN Y ESPECIFICACIONES
                        </div>
                        <div style="padding: 15px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 10px;" cellpadding="5">
                                <tr>
                                    <td style="width: 25%; font-weight: bold; color: #711C37;">Código de Inventario:</td>
                                    <td style="width: 25%; font-weight: bold;">{{ $record->codigo_inventario }}</td>
                                    <td style="width: 25%; font-weight: bold; color: #711C37;">Estado Operativo:</td>
                                    <td style="width: 25%; font-weight: bold;">{{ $record->estado?->label() ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #711C37;">Nombre / Descripción:</td>
                                    <td>{{ $record->nombre_descriptivo }}</td>
                                    <td style="font-weight: bold; color: #711C37;">Número de Serie:</td>
                                    <td><code style="font-family: monospace;">{{ $record->numero_serie ?: '—' }}</code></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #711C37;">Producto Base:</td>
                                    <td>{{ $record->producto?->nombre ?? 'N/A' }}</td>
                                    <td style="font-weight: bold; color: #711C37;">Variante:</td>
                                    <td>{{ $record->variante?->nombre_variante ?: 'Única / Estándar' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #711C37;">Costo Adquisición:</td>
                                    <td style="font-weight: bold; color: #10b981;">
                                        @if($record->costo_adquisicion !== null)
                                            {{ $record->moneda?->simbolo ?? '$' }}{{ number_format($record->costo_adquisicion, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td style="font-weight: bold; color: #711C37;">Fecha Adquisición:</td>
                                    <td>{{ $record->fecha_adquisicion?->format('d/m/Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #711C37;">Proveedor:</td>
                                    <td>
                                        @if($record->proveedor)
                                            {{ $record->proveedor->codigo }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td style="font-weight: bold; color: #711C37;">Vencimiento Garantía:</td>
                                    <td>{{ $record->fecha_garantia_fin?->format('d/m/Y') ?: 'Sin garantía registrada' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #711C37;">Ubicación Actual:</td>
                                    <td colspan="3" style="font-weight: bold; color: #1d4ed8;">
                                        @if($record->asignacionActiva?->asignable)
                                            @php
                                                $tipo = class_basename($record->asignacionActiva->asignable_type);
                                                $prefijo = match ($tipo) {
                                                    'Habitacion' => 'Habitación',
                                                    'Ubicacion' => 'Ubicación / Bodega',
                                                    'Espacio' => 'Espacio / Área Común',
                                                    default => $tipo,
                                                };
                                            @endphp
                                            {{ $prefijo }}: {{ $record->asignacionActiva->asignable->nombre }}
                                        @else
                                            <span style="color: #999; font-style: italic;">Sin asignar</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Historial de Asignaciones y Traslados -->
                    <div style="margin-bottom: 25px;">
                        <h4 style="color: #711C37; border-bottom: 1px solid #711C37; padding-bottom: 5px; font-size: 11px; margin-bottom: 10px; text-transform: uppercase;">Historial de Asignaciones y Traslados</h4>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Destino / Lugar</th>
                                    <th>Tipo de Lugar</th>
                                    <th style="text-align: center;">Desde</th>
                                    <th style="text-align: center;">Hasta</th>
                                    <th>Motivo / Trasladado Por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($record->asignaciones as $asig)
                                <tr>
                                    <td><strong>{{ $asig->asignable?->nombre ?? 'N/A' }}</strong></td>
                                    <td>
                                        @php
                                            $tipo = class_basename($asig->asignable_type);
                                            $prefijo = match ($tipo) {
                                                'Habitacion' => 'Habitación',
                                                'Ubicacion' => 'Ubicación / Bodega',
                                                'Espacio' => 'Espacio / Área Común',
                                                default => $tipo,
                                            };
                                        @endphp
                                        {{ $prefijo }}
                                    </td>
                                    <td style="text-align: center;">{{ $asig->fecha_inicio?->format('d/m/Y') }}</td>
                                    <td style="text-align: center;">{{ $asig->fecha_fin?->format('d/m/Y') ?: 'Vigente / Actual' }}</td>
                                    <td>
                                        <span style="font-size: 9px; color: #555;">{{ $asig->motivo ?: 'Sin motivo registrado' }}</span>
                                        <br><small style="color: #999;">Por: {{ $asig->asignadoPor?->name ?? 'Sistema' }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #999; font-style: italic;">No hay historial de movimientos.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Historial de Mantenimientos -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="color: #711C37; border-bottom: 1px solid #711C37; padding-bottom: 5px; font-size: 11px; margin-bottom: 10px; text-transform: uppercase;">Historial de Intervenciones y Mantenimiento</h4>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descripción de la Intervención</th>
                                    <th style="text-align: center;">Fechas (Inicio / Fin)</th>
                                    <th style="text-align: right;">Costo</th>
                                    <th style="text-align: center;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($record->mantenimientos as $mant)
                                <tr>
                                    <td><strong>{{ $mant->tipo?->label() ?? 'N/A' }}</strong></td>
                                    <td>
                                        <span>{{ $mant->descripcion }}</span>
                                        @if($mant->notas)
                                            <br><small style="color: #666; font-style: italic;">Taller: {{ $mant->notas }}</small>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        {{ $mant->fecha_inicio?->format('d/m/Y') }}<br>
                                        <small style="color: #666;">{{ $mant->fecha_fin?->format('d/m/Y') ?: 'En proceso' }}</small>
                                    </td>
                                    <td style="text-align: right; font-weight: bold;">
                                        @if($mant->costo !== null)
                                            {{ $mant->moneda?->simbolo ?? '$' }}{{ number_format($mant->costo, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge">{{ $mant->estado?->label() ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #999; font-style: italic;">Este activo no registra intervenciones de mantenimiento.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 8px; color: #999;">Este documento es el expediente técnico unificado del activo fijo del Hotel Bugambilias.</td>
                                <td style="text-align: right; font-weight: bold; color: #711C37; text-transform: uppercase;">Sistema de Gestión de Activos</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
