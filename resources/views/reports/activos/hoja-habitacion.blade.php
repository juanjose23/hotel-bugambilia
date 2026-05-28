@extends('layouts.reporte-htb')

@section('report_code', 'HTB-ACT-013')
@section('report_name', 'Hoja de '.($tipo === 'habitacion' ? 'Habitación' : 'Espacio'))

@section('extra-css')
<style>
    .room-info { margin-bottom: 20px; }
    .room-info td { padding: 4px 8px; border: none; vertical-align: top; }
    .room-info .label { font-weight: bold; color: #711C37; width: 140px; }
    .signature-area { margin-top: 40px; }
    .signature-area table { width: 100%; border-collapse: collapse; }
    .signature-area td {
        width: 33%;
        text-align: center;
        padding: 40px 10px 10px;
        border-top: 1px solid #333;
        font-size: 10px;
    }
</style>
@endsection

@section('content')
<div class="report-page">
    <table class="page-frame">
        <tbody>
            <tr>
                <td class="frame-body" style="padding: 40px;">
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
                                    <div class="hdr-title" style="font-size: 14px;">Hoja de {{ $tipo === 'habitacion' ? 'Habitación' : 'Espacio' }}</div>
                                    <div class="hdr-code">HTB-ACT-013</div>
                                    <div class="hdr-sub">{{ $hotelInfo['direccion'] }}</div>
                                    <div class="hdr-sub">Tel: {{ $hotelInfo['telefono'] }} | {{ $hotelInfo['email'] }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 20px; font-size: 10px; color: #666;">
                        <span><strong>Generado en:</strong> {{ $generadoEn }}</span> &nbsp;|&nbsp;
                        <span><strong>Generado por:</strong> {{ $usuario }}</span>
                    </div>

                    <div class="pcard">
                        <div class="pcard-hdr">
                            <table>
                                <tr>
                                    <td>
                                        <div class="pcard-title">{{ $entidad->nombre }}</div>
                                        <div class="pcard-meta">
                                            @if($tipo === 'habitacion')
                                                Habitación {{ $entidad->numero }} — {{ $entidad->categoria?->nombre ?? '' }}
                                            @else
                                                {{ $entidad->tipoEspacio?->nombre ?? 'Espacio' }}
                                            @endif
                                            | Ubicación: {{ $entidad->ubicacion?->nombre ?? '—' }}
                                        </div>
                                    </td>
                                    <td style="text-align:right;">
                                        <span class="badge" style="background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;">
                                            {{ $tipo === 'habitacion' ? 'Habitación' : 'Espacio' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <table style="width:100%;border-collapse:collapse;">
                            <tr>
                                <td style="padding:12px;">
                                    <table class="room-info">
                                        @if($tipo === 'habitacion')
                                        <tr><td class="label">Código:</td><td>{{ $entidad->codigo }}</td></tr>
                                        <tr><td class="label">Número:</td><td>{{ $entidad->numero }}</td></tr>
                                        <tr><td class="label">Categoría:</td><td>{{ $entidad->categoria?->nombre ?? '—' }}</td></tr>
                                        <tr><td class="label">Piso/Ubicación:</td><td>{{ $entidad->ubicacion?->nombre ?? '—' }}</td></tr>
                                        <tr><td class="label">Teléfono:</td><td>{{ $entidad->detalle?->telefono ?? '—' }}</td></tr>
                                        @else
                                        <tr><td class="label">Código:</td><td>{{ $entidad->codigo }}</td></tr>
                                        <tr><td class="label">Tipo:</td><td>{{ $entidad->tipo?->getLabel() ?? '—' }}</td></tr>
                                        <tr><td class="label">Capacidad:</td><td>{{ $entidad->capacidad_personas ?? '—' }} personas</td></tr>
                                        <tr><td class="label">Espacio Padre:</td><td>{{ $entidad->padre?->nombre ?? 'Espacio principal' }}</td></tr>
                                        <tr><td class="label">Ubicación:</td><td>{{ $entidad->ubicacion?->nombre ?? '—' }}</td></tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="pcard" style="margin-top:12px;">
                        <div class="pcard-hdr">
                            <div class="pcard-title">Inventario de Activos Asignados</div>
                            <div class="pcard-meta">Total: <strong>{{ $activos->count() }}</strong> activos</div>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Activo</th>
                                    <th>Producto</th>
                                    <th>Nro. Serie</th>
                                    <th>Estado</th>
                                    <th style="text-align:right;">Costo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activos as $asignacion)
                                @php $a = $asignacion->activo; @endphp
                                <tr>
                                    <td><strong>{{ $a->codigo_inventario }}</strong></td>
                                    <td>{{ $a->nombre_descriptivo }}</td>
                                    <td>{{ $a->producto?->nombre ?? '—' }}</td>
                                    <td><code style="font-family:monospace;">{{ $a->numero_serie ?: '—' }}</code></td>
                                    <td style="text-align:center;"><span class="badge">{{ $a->estado?->label() }}</span></td>
                                    <td style="text-align:right;font-weight:bold;">
                                        @if($a->costo_adquisicion !== null)
                                            {{ $a->moneda?->simbolo ?? '$' }}{{ number_format($a->costo_adquisicion, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" style="text-align:center;color:#999;font-style:italic;">No hay activos asignados a esta {{ $tipo === 'habitacion' ? 'habitación' : 'ubicación' }}.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top:16px;font-size:9px;color:#666;">
                        <strong>Valor total de activos en {{ $tipo === 'habitacion' ? 'habitación' : 'espacio' }}:</strong>
                        ${{ number_format($activos->sum(fn($asig) => (float) ($asig->activo->costo_adquisicion ?? 0)), 2) }}
                    </div>

                    <div class="signature-area">
                        <table>
                            <tr>
                                <td>Responsable de entrega</td>
                                <td>Recibe conforme</td>
                                <td>Vo.Bo. Administración</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="frame-footer">
                    <div class="doc-footer">
                        <table style="width:100%;">
                            <tr>
                                <td style="font-size:8px;color:#999;">Documento de inventario físico para entregas y revisiones.</td>
                                <td style="text-align:right;font-weight:bold;color:#711C37;text-transform:uppercase;">Sistema de Gestión de Activos</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
