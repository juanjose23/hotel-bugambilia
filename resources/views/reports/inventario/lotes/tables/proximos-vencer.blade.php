<table class="data-table">
    <thead>
        <tr>
            <th>Código de Lote</th>
            <th>Producto</th>
            <th>Variante</th>
            <th>Ubicación / Almacén</th>
            <th style="text-align: right;">Cantidad Disponible</th>
            <th>Fecha Vencimiento</th>
            <th style="text-align: center;">Días Restantes</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $lote)
            @php
                $diasRestantes = now()->diffInDays(\Carbon\Carbon::parse($lote->fecha_vencimiento), false);
            @endphp
            <tr>
                <td><span style="font-family: monospace; font-weight: bold; color: #d97706;">{{ $lote->codigo_lote }}</span></td>
                <td><strong>{{ $lote->producto?->nombre }}</strong></td>
                <td>{{ $lote->variante?->nombre_variante ?? $lote->variante?->codigo ?? 'N/A' }}</td>
                <td>{{ $lote->ubicacion?->nombre ?? 'N/A' }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($lote->cantidad_disponible, 2) }}</td>
                <td style="color: #ea580c; font-weight: bold;">{{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}</td>
                <td style="text-align: center;">
                    <span class="badge" style="background: #fef3c7; color: #d97706; border: 1px solid #fde68a;">
                        {{ (int)$diasRestantes }} días
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #718096; padding: 20px;">No hay lotes próximos a vencer en los siguientes días.</td>
            </tr>
        @endforelse
    </tbody>
</table>
