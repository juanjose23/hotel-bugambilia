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
            @if($activo)
                <div style="margin-bottom:16px;background:#f8fafc;padding:12px 15px;border:1px solid #e2e8f0;border-radius:4px;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr>
                            <td style="width:50%;vertical-align:top;border:none;padding:0;">
                                <strong style="color:#711C37;font-size:11px;text-transform:uppercase;">Activo:</strong><br>
                                @include('reports.activos.partials.sku', ['codigo' => $activo->codigo_inventario, 'fontSize' => '13px'])
                                <br><strong>{{ $activo->producto?->nombre ?? '—' }}</strong>
                            </td>
                            <td style="width:50%;vertical-align:top;border:none;padding:0;text-align:right;">
                                @if($filtroActivo)
                                    <span class="badge" style="background:#dbeafe;color:#1e40af;border:1px solid #1e40af;font-size:8px;">
                                        Historial de un activo
                                    </span>
                                @else
                                    <span class="badge" style="background:#d1fae5;color:#065f46;border:1px solid #065f46;font-size:8px;">
                                        Historial general
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

            @forelse($lineaTiempo as $evento)
                @include('reports.activos.partials.timeline-item', ['evento' => $evento])
            @empty
                @include('reports.activos.partials.empty-state', ['type' => 'div', 'mensaje' => 'No se registran movimientos para este activo.'])
            @endforelse
        </div>

        <div class="report-footer">
            @include('reports.layout.partials.footer', [
                'generadoEn' => $generadoEn ?? now()->format('d/m/Y H:i'),
                'usuario' => $usuario ?? 'Sistema',
            ])
        </div>
    </div>
@endsection
