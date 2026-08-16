@extends('reports.layout.app', [
    'nombreReporte' => $titulo,
    'codigoReporte' => $codigo,
])

@section('extra-css')
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
                        <th>Codigo Cliente</th>
                        <th>Nombre del Titular</th>
                        <th>Identificacion</th>
                        <th>Correo Electronico</th>
                        <th>Telefono</th>
                        <th>Reservas Realizadas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                        <tr>
                            <td><span class="sku-code">{{ $cliente->codigo_cliente }}</span></td>
                            <td>{{ $cliente->persona?->nombre_completo ?? 'N/A' }}</td>
                            <td>{{ $cliente->persona?->numero_identificacion ?? 'N/A' }}</td>
                            <td>{{ $cliente->persona?->email ?? 'N/A' }}</td>
                            <td>{{ $cliente->persona?->telefono ?? 'N/A' }}</td>
                            <td>{{ $cliente->reservas->count() }} estadia(s)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-row">No se encontraron registros de clientes.</td>
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
