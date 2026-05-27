@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-003')
@section('report_name', 'Ficha Técnica de Orden de Mantenimiento')

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
                                    <div class="hdr-title" style="font-size: 14px;">Orden de Mantenimiento</div>
                                    <div class="hdr-code">HTB-ACT-003</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Datos Generales de la Orden -->
                    <div style="margin-bottom: 25px; background: #fff; border: 1px solid #711C37; border-radius: 4px; overflow: hidden;">
                        <div style="background: #711C37; padding: 10px 15px; color: #fff; font-weight: bold; font-size: 11px;">
                            IDENTIFICACIÓN DE LA ORDEN DE INTERVENCIÓN
                        </div>
                        <div style="padding: 15px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 10px;" cellpadding="5">
                                <tr>
                                    <td style="width: 25%; font-weight: bold; color: #711C37;">Orden Nro:</td>
                                    <td style="width: 25%; font-weight: bold;">{{ $record->id }}</td>
                                    <td style="width: 25%; font-weight: bold; color: #711C37;">Estado Actual:</td>
                                    <td style="width: 25%; font-weight: bold;">
                                        <span class="badge" style="background: #e2e8f0; color: #475569;">{{ $record->estado?->label() ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #711C37;">Tipo Mantenimiento:</td>
                                    <td>{{ $record->tipo?->label() ?? 'N/A' }}</td>
                                    <td style="font-weight: bold; color: #711C37;">Fecha de Ingreso:</td>
                                    <td>{{ $record->fecha_inicio?->format('d/m/Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #711C37;">Fecha de Finalización:</td>
                                    <td>{{ $record->fecha_fin?->format('d/m/Y') ?: 'En taller / Pendiente' }}</td>
                                    <td style="font-weight: bold; color: #711C37;">Técnico / Responsable:</td>
                                    <td>{{ $record->realizadoPor?->name ?? 'No asignado' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Datos del Activo Intervenido -->
                    <div style="margin-bottom: 25px; background: #fff; border: 1px solid #711C37; border-radius: 4px; overflow: hidden;">
                        <div style="background: #711C37; padding: 10px 15px; color: #fff; font-weight: bold; font-size: 11px;">
                            ACTIVO FIJO OBJETO DE LA INTERVENCIÓN
                        </div>
                        <div style="padding: 15px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 10px;" cellpadding="5">
                                <tr>
                                    <td style="width: 25%; font-weight: bold; color: #711C37;">Código Inventario:</td>
                                    <td style="width: 25%; font-weight: bold;">{{ $record->activo?->codigo_inventario }}</td>
                                    <td style="width: 25%; font-weight: bold; color: #711C37;">Nombre del Activo:</td>
                                    <td style="width: 25%;">{{ $record->activo?->nombre_descriptivo }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #711C37;">Categoría / Producto:</td>
                                    <td>{{ $record->activo?->producto?->nombre ?? 'N/A' }}</td>
                                    <td style="font-weight: bold; color: #711C37;">Número de Serie:</td>
                                    <td><code style="font-family: monospace;">{{ $record->activo?->numero_serie ?: '—' }}</code></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #711C37;">Estado Operativo Activo:</td>
                                    <td colspan="3">{{ $record->activo?->estado?->label() ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Detalles Técnicos, Costos y Taller -->
                    <div style="margin-bottom: 25px; background: #fff; border: 1px solid #711C37; border-radius: 4px; overflow: hidden;">
                        <div style="background: #711C37; padding: 10px 15px; color: #fff; font-weight: bold; font-size: 11px;">
                            INFORMACIÓN ECONÓMICA Y DE RESOLUCIÓN TÉCNICA
                        </div>
                        <div style="padding: 15px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 10px;" cellpadding="5">
                                <tr>
                                    <td style="width: 25%; font-weight: bold; color: #711C37;">Costo del Mantenimiento:</td>
                                    <td style="width: 25%; font-weight: bold; color: #10b981;">
                                        @if($record->costo !== null)
                                            {{ $record->moneda?->simbolo ?? '$' }}{{ number_format($record->costo, 2) }}
                                        @else
                                            — (Mantenimiento interno / Garantía)
                                        @endif
                                    </td>
                                    <td style="width: 25%; font-weight: bold; color: #711C37;">Taller / Proveedor Externo:</td>
                                    <td style="width: 25%;">
                                        @if($record->proveedor)
                                            {{ $record->proveedor->codigo }} - {{ $record->proveedor->persona->personaJuridica->razon_social ?? $record->proveedor->persona->nombre_completo }}
                                        @else
                                            — (Mantenimiento Interno)
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #711C37; vertical-align: top;">Descripción de Falla / Diagnóstico:</td>
                                    <td colspan="3" style="line-height: 1.4;">{{ $record->descripcion }}</td>
                                </tr>
                                @if($record->notas)
                                <tr>
                                    <td style="font-weight: bold; color: #711C37; vertical-align: top;">Informe de Reparación / Taller:</td>
                                    <td colspan="3" style="line-height: 1.4; font-style: italic; color: #475569;">{{ $record->notas }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Cuadro de Firmas de Taller / Aceptación -->
                    <div style="margin-top: 50px;">
                        <table style="width: 100%; text-align: center; font-size: 9px;" cellpadding="15">
                            <tr>
                                <td style="width: 33.3%;">
                                    <div style="border-top: 1px solid #94a3b8; width: 80%; margin: 0 auto; padding-top: 5px;">
                                        Técnico que Interviene
                                    </div>
                                </td>
                                <td style="width: 33.3%;">
                                    <div style="border-top: 1px solid #94a3b8; width: 80%; margin: 0 auto; padding-top: 5px;">
                                        Responsable de Almacén / Activos
                                    </div>
                                </td>
                                <td style="width: 33.3%;">
                                    <div style="border-top: 1px solid #94a3b8; width: 80%; margin: 0 auto; padding-top: 5px;">
                                        Aprobación Gerencia
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 8px; color: #999;">Este documento constituye el registro de intervención técnica oficial del activo del Hotel Bugambilias.</td>
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
