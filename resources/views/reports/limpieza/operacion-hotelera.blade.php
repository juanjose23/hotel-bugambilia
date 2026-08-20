@extends('reports.layout.app', [
    'nombreReporte' => $nombreReporte,
    'codigoReporte' => $codigoReporte,
])

@section('extra-css')
    <style>
        .kpi-grid { width: 100%; margin-bottom: 10px; }
        .kpi-grid td { width: 25%; padding: 5px; border: 0; }
        .kpi-card { border: 1px solid #c5d0df; background: #f8fafc; padding: 8px; border-radius: 4px; }
        .kpi-value { font-size: 15pt; font-weight: bold; color: #711C37; line-height: 1.1; }
        .kpi-label { font-size: 7pt; color: #64748b; text-transform: uppercase; margin-top: 2px; }
        .small-table th { font-size: 7.4pt; padding: 5px; }
        .small-table td { font-size: 7.3pt; padding: 4px 5px; }
        .section-block { page-break-inside: avoid; margin-bottom: 8px; }
        .report-page-break { page-break-after: always; }
    </style>
@endsection

@section('content')
    @php
        $secciones = $secciones ?? ['tiempos', 'pendientes', 'amenities', 'productividad'];
        $debeMostrarKpis = count($secciones) > 1;
    @endphp

    @if($debeMostrarKpis)
        <table class="kpi-grid">
        <tr>
            <td>
                <div class="kpi-card">
                    <div class="kpi-value">{{ $resumen['tiempo_promedio_minutos'] ?? 0 }} min</div>
                    <div class="kpi-label">Tiempo promedio</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-value">{{ $resumen['pendientes'] ?? 0 }}</div>
                    <div class="kpi-label">Habitaciones pendientes</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-value">{{ $resumen['bloqueadas'] ?? 0 }}</div>
                    <div class="kpi-label">Habitaciones bloqueadas</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-value">{{ $resumen['finalizadas'] ?? 0 }}</div>
                    <div class="kpi-label">Limpiezas finalizadas</div>
                </div>
            </td>
        </tr>
        </table>
    @endif

    @foreach($secciones as $index => $seccion)
        @if($index > 0)
            <div class="report-page-break"></div>
            <div class="page-top-spacer"></div>
        @endif

        @if($seccion === 'tiempos')
            <div class="section-block">
                <div class="section-title">Tiempo promedio de limpieza por habitación</div>
                <table class="data-table small-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Fecha</th>
                            <th style="width: 22%;">Habitación / área</th>
                            <th style="width: 18%;">Colaborador</th>
                            <th style="width: 16%;">Turno</th>
                            <th style="width: 12%;">Minutos</th>
                            <th style="width: 20%;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tiempos_por_habitacion as $item)
                            <tr>
                                <td>{{ $item['fecha'] }}</td>
                                <td>{{ $item['habitacion'] }}</td>
                                <td>{{ $item['colaborador'] }}</td>
                                <td>{{ $item['turno'] }}</td>
                                <td class="text-right">{{ $item['minutos'] }}</td>
                                <td>{{ $item['estado'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="empty-row">No hay limpiezas finalizadas con hora de inicio y fin en el período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        @if($seccion === 'pendientes')
            <div class="section-block">
                <div class="section-title">Habitaciones pendientes / bloqueadas</div>
                <table class="data-table small-table">
                    <thead>
                        <tr>
                            <th style="width: 26%;">Habitación / área</th>
                            <th style="width: 18%;">Estado</th>
                            <th style="width: 36%;">Motivo</th>
                            <th style="width: 20%;">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendientes_bloqueadas as $item)
                            <tr>
                                <td>{{ $item['habitacion'] }}</td>
                                <td>{{ $item['estado'] }}</td>
                                <td>{{ $item['motivo'] }}</td>
                                <td>{{ $item['fecha'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty-row">No hay habitaciones pendientes ni bloqueadas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        @if($seccion === 'amenities')
            <div class="section-block">
                <div class="section-title">Consumo de amenities por habitación</div>
                <table class="data-table small-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Habitación / área</th>
                            <th style="width: 45%;">Amenity / producto</th>
                            <th style="width: 20%;">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($amenities_por_habitacion as $item)
                            <tr>
                                <td>{{ $item['habitacion'] }}</td>
                                <td>{{ $item['producto'] }}</td>
                                <td class="text-right">{{ number_format((float) $item['cantidad'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-row">No hay consumos de amenities registrados en el período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        @if($seccion === 'productividad')
            <div class="section-block">
                <div class="section-title">Productividad por colaborador / turno</div>
                <table class="data-table small-table">
                    <thead>
                        <tr>
                            <th style="width: 28%;">Colaborador</th>
                            <th style="width: 22%;">Turno</th>
                            <th style="width: 15%;">Asignadas</th>
                            <th style="width: 15%;">Finalizadas</th>
                            <th style="width: 20%;">Promedio min.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productividad as $item)
                            <tr>
                                <td>{{ $item['colaborador'] }}</td>
                                <td>{{ $item['turno'] }}</td>
                                <td class="text-right">{{ $item['asignadas'] }}</td>
                                <td class="text-right">{{ $item['finalizadas'] }}</td>
                                <td class="text-right">{{ $item['promedio_minutos'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-row">No hay productividad registrada en el período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach
@endsection
