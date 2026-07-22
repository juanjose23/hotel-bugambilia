<div style="margin-bottom:12px;background:#f8fafc;padding:10px 15px;border-radius:4px;border:1px solid #e2e8f0;font-size:9px;">
    <h3 style="color:#711C37;font-size:10px;margin-bottom:5px;text-transform:uppercase;">Información del Lote Analizado</h3>
    <table style="width:100%;border-collapse:collapse;margin-top:4px;">
        <tr>
            <td style="border:none;padding:2px 0;width:15%;"><strong>Código Lote:</strong></td>
            <td style="border:none;padding:2px 0;width:35%;font-family:monospace;font-weight:bold;color:#711C37;">{{ $lote->codigoLote }}</td>
            <td style="border:none;padding:2px 0;width:15%;"><strong>Producto:</strong></td>
            <td style="border:none;padding:2px 0;width:35%;">{{ $lote->producto }} ({{ $lote->variante }})</td>
        </tr>
        <tr>
            <td style="border:none;padding:2px 0;"><strong>Disponible:</strong></td>
            <td style="border:none;padding:2px 0;font-weight:bold;">{{ number_format($lote->cantidadDisponible, 2) }}</td>
            <td style="border:none;padding:2px 0;"><strong>Ubicación Actual:</strong></td>
            <td style="border:none;padding:2px 0;">{{ $lote->ubicacion }}</td>
        </tr>
        <tr>
            <td style="border:none;padding:2px 0;"><strong>Fecha Vence:</strong></td>
            <td style="border:none;padding:2px 0;color:#ea580c;">{{ $lote->fechaVencimiento ? $lote->fechaVencimiento->format('d/m/Y') : 'Sin vencimiento' }}</td>
            <td style="border:none;padding:2px 0;"><strong>Estado:</strong></td>
            <td style="border:none;padding:2px 0;">
                <span class="badge" style="background:#e0f2fe;color:#0369a1;border:1px solid #7dd3fc;">
                    {{ $lote->estado instanceof \App\Enums\Inventario\EstadoLote ? $lote->estado->label() : '' }}
                </span>
            </td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Fecha Movimiento</th>
            <th>Tipo</th>
            <th>Producto</th>
            <th>Ubicación Origen</th>
            <th>Ubicación Destino</th>
            <th style="text-align: right;">Cantidad Transada</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $mov)
            <tr>
                <td>{{ $mov->fecha ? $mov->fecha->format('d/m/Y H:i') : 'N/A' }}</td>
                <td>
                    <span class="badge badge-on" style="background: #e0f2fe; color: #0369a1; border-color: #7dd3fc;">
                        {{ $mov->tipo }}
                    </span>
                </td>
                <td><strong>{{ $mov->producto }}</strong></td>
                <td>{{ $mov->ubicacionOrigen ?? 'N/A' }}</td>
                <td>{{ $mov->ubicacionDestino ?? 'N/A' }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($mov->cantidad, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #718096; padding: 20px;">No se registran movimientos de trazabilidad para este lote.</td>
            </tr>
        @endforelse
    </tbody>
</table>
