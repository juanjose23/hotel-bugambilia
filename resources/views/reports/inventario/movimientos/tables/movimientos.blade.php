<table class="data-table">
    <thead>
        <tr>
            <th>Fecha / Hora</th>
            <th>Tipo Mov.</th>
            <th>Producto</th>
            <th>Lote</th>
            <th>Origen</th>
            <th>Destino</th>
            <th style="text-align: right;">Cantidad</th>
            <th>Referencia</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $mov)
            <tr>
                <td>{{ $mov->created_at?->format('d/m/Y H:i') }}</td>
                <td>
                    <span class="badge badge-on" style="background: #e0f2fe; color: #0369a1; border-color: #7dd3fc;">
                        {{ $mov->tipo }}
                    </span>
                </td>
                <td><strong>{{ $mov->producto?->nombre }}</strong></td>
                <td><span style="font-family: monospace;">{{ $mov->lote?->codigo_lote ?? 'N/A' }}</span></td>
                <td>{{ $mov->ubicacionOrigen?->nombre ?? 'N/A' }}</td>
                <td>{{ $mov->ubicacionDestino?->nombre ?? 'N/A' }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format((float) $mov->cantidad, 2) }}</td>
                <td>{{ $mov->referencia ?? 'N/A' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #718096; padding: 20px;">No se registraron movimientos en este período.</td>
            </tr>
        @endforelse
    </tbody>
</table>
