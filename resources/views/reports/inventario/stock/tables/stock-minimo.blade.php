<table class="data-table">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Variante</th>
            <th>Categoría</th>
            <th style="text-align: right;">Stock en Almacén</th>
            <th style="text-align: right;">Punto de Pedido (Ideal)</th>
            <th style="text-align: right;">Pendiente Reabastecer</th>
            <th style="text-align: center;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $row)
            <tr>
                <td><strong>{{ $row->producto }}</strong></td>
                <td>{{ $row->variante ?? 'Sin Variante / Base' }}</td>
                <td>{{ $row->categoria ?? 'N/A' }}</td>
                <td style="text-align: right; font-weight: bold; color: {{ $row->stockActual <= 0 ? '#b91c1c' : '#b45309' }};">
                    {{ number_format($row->stockActual, 2) }}
                </td>
                <td style="text-align: right;">{{ number_format($row->puntoPedido, 2) }}</td>
                <td style="text-align: right; font-weight: bold; color: #b91c1c;">
                    {{ number_format($row->pendienteReplenish, 2) }}
                </td>
                <td style="text-align: center;">
                    @php
                        $color = match($row->estado) {
                            'Crítico' => 'background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;',
                            'Reordenar' => 'background: #fef3c7; color: #d97706; border: 1px solid #fde68a;',
                            default => 'background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7;',
                        };
                    @endphp
                    <span class="badge" style="{{ $color }}">
                        {{ $row->estado }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #718096; padding: 20px;">No hay productos bajo el punto de pedido o stock mínimo.</td>
            </tr>
        @endforelse
    </tbody>
</table>
