<table class="data-table">
    <thead>
        <tr>
            <th>Código de Lote</th>
            <th>Producto</th>
            <th>Variante</th>
            <th>Ubicación / Almacén</th>
            <th style="text-align: right;">Cantidad Inicial</th>
            <th style="text-align: right;">Disponible</th>
            <th>Fecha Retención</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $lote)
            <tr>
                <td><span style="font-family: monospace; font-weight: bold; color: #b45309;">{{ $lote->codigo_lote }}</span></td>
                <td><strong>{{ $lote->producto?->nombre }}</strong></td>
                <td>{{ $lote->variante?->nombre_variante ?? $lote->variante?->codigo ?? 'N/A' }}</td>
                <td>{{ $lote->ubicacion?->nombre ?? 'N/A' }}</td>
                <td style="text-align: right;">{{ number_format($lote->cantidad_inicial, 2) }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($lote->cantidad_disponible, 2) }}</td>
                <td>{{ $lote->updated_at?->format('d/m/Y H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #718096; padding: 20px;">No hay lotes en cuarentena.</td>
            </tr>
        @endforelse
    </tbody>
</table>
