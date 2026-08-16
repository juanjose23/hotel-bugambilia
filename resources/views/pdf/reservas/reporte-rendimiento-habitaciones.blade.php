@extends('reports.layout.app', [
    'nombreReporte' => $titulo,
    'codigoReporte' => $codigo,
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
    .empty-row { text-align: center; color: #64748b; padding: 14px; }
@endsection

@section('content')
    <div class="pagina">
        <div class="report-header">
            @include('reports.layout.partials.header', [
                'logo_base64' => $datosHotel['logo_base64'] ?? null,
                'hotelInfo' => is_array($datosHotel['hotelInfo'] ?? null) ? $datosHotel['hotelInfo'] : [],
            ])
        </div>

        <div class="report-content">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Capacidad Maxima</th>
                        <th>Habitaciones Activas</th>
                        <th class="amount">Precio Base / Noche</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categorias as $categoria)
                        <tr>
                            <td><span class="sku-code">{{ $categoria['nombre'] }}</span></td>
                            <td>Hasta {{ $categoria['capacidad_total'] }} personas</td>
                            <td>{{ $categoria['habitaciones_activas'] }} unidad(es)</td>
                            <td class="amount">$ {{ number_format((float) ($categoria['precio_base_noche'] ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-row">No hay categorias registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'usuario' => auth()->user()?->name ?? 'Sistema',
                'generadoEn' => now()->format('d/m/Y H:i'),
            ])
        </div>
    </div>
@endsection
