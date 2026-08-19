@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Ficha de Activo Fijo',
    'codigo' => $codigoReporte ?? 'HTB-ACT-002',
])

@section('content')
    <div style="margin-bottom: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                    <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Código de Inventario</strong><br>
                    @include('reports.activos.partials.sku', ['codigo' => $record->codigo_inventario, 'fontSize' => '12pt'])
                </td>
                <td style="width: 50%; vertical-align: top; border: none; padding: 0; text-align: right;">
                    @include('reports.activos.partials.estado-activo', ['estado' => $record->estado, 'style' => 'font-size:9px;'])
                </td>
            </tr>
        </table>
    </div>

    @include('reports.activos.partials.info-grid', [
        'campos' => [
            ['label' => 'Producto', 'value' => $record->producto?->nombre],
            ['label' => 'Variante', 'value' => $record->variante?->codigo ?? 'Sin variante'],
            ['label' => 'Proveedor', 'value' => $record->proveedor?->persona?->nombre_completo],
            ['label' => 'Costo Adquisición', 'value' => $record->costo_adquisicion, 'isCosto' => true, 'monedaSimbolo' => $record->moneda?->simbolo],
            ['label' => 'Fecha Adquisición', 'value' => $record->fecha_adquisicion?->format('d/m/Y')],
            ['label' => 'Garantía Hasta', 'value' => $record->fecha_garantia_fin?->format('d/m/Y')],
            ['label' => 'Vida Útil', 'value' => ($record->vida_util_meses ?? '—') . ' meses'],
            ['label' => 'Ubicación Actual', 'value' => $record->asignacionActiva?->destinoLabel() ?? 'Sin asignar'],
        ],
    ])

    @if($record->asignaciones && count($record->asignaciones) > 0)
        <div style="margin-bottom: 16px;" class="avoid-break">
            @include('reports.activos.partials.section-title', ['titulo' => 'Historial de Asignaciones'])
            <table class="data-table" style="margin-top: 6px;">
                <thead>
                    <tr>
                        <th>Destino</th>
                        <th style="text-align: center;">Fecha Inicio</th>
                        <th style="text-align: center;">Fecha Fin</th>
                        <th>Motivo</th>
                        <th>Asignado Por</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($record->asignaciones as $asig)
                        <tr>
                            <td>{{ $asig->destinoLabel() }}</td>
                            <td style="text-align: center;">{{ $asig->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                            <td style="text-align: center;">{{ $asig->fecha_fin?->format('d/m/Y') ?? 'Activo' }}</td>
                            <td style="font-size: 8pt; color: #64748b;">{{ $asig->motivo ?? '—' }}</td>
                            <td style="font-size: 8pt;">{{ $asig->asignadoPor?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($record->mantenimientos && count($record->mantenimientos) > 0)
        <div style="margin-bottom: 16px;" class="avoid-break">
            @include('reports.activos.partials.section-title', ['titulo' => 'Historial de Mantenimientos'])
            <table class="data-table" style="margin-top: 6px;">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Plan / Descripción</th>
                        <th style="text-align: center;">Fecha Programada</th>
                        <th style="text-align: center;">Estado</th>
                        <th style="text-align: right;">Costo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($record->mantenimientos as $mtto)
                        <tr>
                            <td>{{ $mtto->tipo?->label() ?? '—' }}</td>
                            <td style="font-size: 8pt;">{{ $mtto->plan?->descripcion ?? '—' }}</td>
                            <td style="text-align: center;">{{ $mtto->fecha_programada?->format('d/m/Y') ?? '—' }}</td>
                            <td style="text-align: center;">
                                @include('reports.activos.partials.estado-activo', ['estado' => $mtto->estado, 'scope' => 'mantenimiento', 'style' => 'font-size:7px;'])
                            </td>
                            <td style="text-align: right;">
                                @include('reports.activos.partials.costo', ['monto' => $mtto->costo_real ?? 0, 'monedaSimbolo' => '$'])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
