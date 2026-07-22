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

        <div class="report-content">
            @include('reports.activos.partials.section-bar', [
                'titulo' => 'Hoja de ' . ($tipo === 'habitacion' ? 'Habitación' : 'Espacio'),
                'subtitulo' => $entidad->nombre ?? '—',
            ])

            @if($tipo === 'habitacion')
                @include('reports.activos.partials.info-grid', [
                    'wrapperStyle' => 'margin-bottom:16px;',
                    'campos' => [
                        ['label' => 'Categoría', 'value' => $entidad->categoria?->nombre],
                        ['label' => 'Ubicación', 'value' => $entidad->ubicacion?->nombre],
                        ['label' => 'Detalle', 'value' => $entidad->detalle?->nombre],
                    ],
                ])
            @else
                @include('reports.activos.partials.info-grid', [
                    'wrapperStyle' => 'margin-bottom:16px;',
                    'campos' => [
                        ['label' => 'Padre', 'value' => $entidad->padre?->nombre],
                        ['label' => 'Ubicación', 'value' => $entidad->ubicacion?->nombre],
                    ],
                ])
            @endif

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código Inventario</th>
                        <th>Producto</th>
                        <th style="text-align: center;">Estado</th>
                        <th style="text-align: right;">Costo</th>
                        <th style="text-align: center;">Fecha Asignación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activos as $asignacion)
                        @php $activo = $asignacion->activo; @endphp
                        <tr>
                            <td>@include('reports.activos.partials.sku', ['codigo' => $activo?->codigo_inventario ?? '—'])</td>
                            <td><strong>{{ $activo?->producto?->nombre ?? '—' }}</strong></td>
                            <td style="text-align: center;">
                                @include('reports.activos.partials.estado-activo', ['estado' => $activo?->estado])
                            </td>
                            <td style="text-align: right;">
                                @include('reports.activos.partials.costo', ['monto' => $activo?->costo_adquisicion ?? 0, 'monedaSimbolo' => $activo?->moneda?->simbolo])
                            </td>
                            <td style="text-align: center;">{{ $asignacion->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        @include('reports.activos.partials.empty-state', ['colspan' => 5, 'mensaje' => 'No hay activos asignados a esta ' . ($tipo === 'habitacion' ? 'habitación' : 'espacio') . '.'])
                    @endforelse
                </tbody>
                @if(count($activos) > 0)
                    @include('reports.activos.partials.table-total', [
                        'labelColspan' => 3,
                        'label' => 'Total Activos:',
                        'total' => $activos->sum(fn ($a) => (float) ($a->activo?->costo_adquisicion ?? 0)),
                        'count' => count($activos),
                    ])
                @endif
            </table>
        </div>

        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'generadoEn' => $generadoEn ?? now()->format('d/m/Y H:i'),
                'usuario' => $usuario ?? 'Sistema',
            ])
        </div>
    </div>
@endsection
