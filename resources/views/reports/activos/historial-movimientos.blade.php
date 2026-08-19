@extends('reports.layout.app', [
    'titulo' => $nombreReporte ?? 'Historial de Movimientos de Activos',
    'codigo' => $codigoReporte ?? 'HTB-ACT-006',
])

@section('extra-css')
    .amount { text-align: right; white-space: nowrap; }
    .empty-row { text-align: center; color: #64748b; padding: 14px; }
@endsection

@section('content')
    @if($activo)
        <div style="margin-bottom: 16px; background: #f8fafc; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 4px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                        <strong style="color: #711C37; font-size: 8pt; text-transform: uppercase;">Activo:</strong><br>
                        @include('reports.activos.partials.sku', ['codigo' => $activo->codigo_inventario, 'fontSize' => '11pt'])
                        <br><strong>{{ $activo->producto?->nombre ?? '—' }}</strong>
                    </td>
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0; text-align: right;">
                        @if($filtroActivo)
                            <span class="badge badge-info">Historial de un activo</span>
                        @else
                            <span class="badge badge-success">Historial general</span>
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
@endsection
