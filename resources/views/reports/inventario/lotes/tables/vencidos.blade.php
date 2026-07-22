<table class="data-table">
    <thead>
        <tr>
            <th>Código de Lote</th>
            <th>Producto</th>
            <th>Variante</th>
            <th>Ubicación / Almacén</th>
            <th style="text-align: right;">Cantidad Disponible</th>
            <th>Fecha Vencimiento</th>
            <th style="text-align: center;">Días de Vencido</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $lote)
            @php
                $diasVencido = now()->diffInDays(\Carbon\Carbon::parse($lote->fecha_vencimiento), false);
            @endphp
            <tr>
                <td><span style="font-family: monospace; font-weight: bold; color: #b91c1c;">{{ $lote->codigo_lote }}</span></td>
                <td><strong>{{ $lote->producto?->nombre }}</strong></td>
                <td>{{ $lote->variante?->nombre_variante ?? $lote->variante?->codigo ?? 'N/A' }}</td>
                <td>{{ $lote->ubicacion?->nombre ?? 'N/A' }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($lote->cantidad_disponible, 2) }}</td>
                <td style="color: #b91c1c; font-weight: bold;">{{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}</td>
                <td style="text-align: center;">
                    <span class="badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;">
                        Vencido hace {{ (int)abs($diasVencido) }} días
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #718096; padding: 20px;">No hay lotes vencidos en stock actual.</td>
            </tr>
        @endforelse
    </tbody>
</table>
