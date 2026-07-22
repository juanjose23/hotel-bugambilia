<table class="data-table">
    <thead>
        <tr>
            <th>Fecha / Hora</th>
            <th>Producto</th>
            <th>Lote</th>
            <th>Origen</th>
            <th>Destino</th>
            <th style="text-align: right;">Cantidad Ajustada</th>
            <th>Responsable</th>
            <th>Motivo / Referencia</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $mov)
            <tr>
                <td>{{ $mov->created_at?->format('d/m/Y H:i') }}</td>
                <td><strong>{{ $mov->producto?->nombre }}</strong></td>
                <td><span style="font-family: monospace;">{{ $mov->lote?->codigo_lote ?? 'N/A' }}</span></td>
                <td>{{ $mov->ubicacionOrigen?->nombre ?? 'N/A' }}</td>
                <td>{{ $mov->ubicacionDestino?->nombre ?? 'N/A' }}</td>
                <td style="text-align: right; font-weight: bold; color: #b91c1c;">
                    {{ number_format((float) $mov->cantidad, 2) }}
                </td>
                <td>{{ $mov->usuario_nombre }}</td>
                <td>{{ $mov->referencia ?? 'Ajuste manual de inventario' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #718096; padding: 20px;">No se registraron ajustes manuales de inventario en este período.</td>
            </tr>
        @endforelse
    </tbody>
</table>
