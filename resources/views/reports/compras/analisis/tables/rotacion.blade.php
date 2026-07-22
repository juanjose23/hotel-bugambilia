<table class="data-table">
    <thead>
        <tr>
            <th>Producto / Artículo</th>
            <th>Código Variante</th>
            <th style="text-align: center;">Cantidad Comprada</th>
            <th style="text-align: right;">Costo Total Acumulado</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalCant = 0;
            $totalGral = 0;
        @endphp
        @forelse($items as $item)
            @php
                $totalCant += $item->total_cantidad;
                $totalGral += $item->total_costo;
            @endphp
            <tr>
                <td><strong>{{ $item->producto_nombre }}</strong></td>
                <td style="color: #666;">{{ $item->variante_codigo ?? 'Sin Variante' }}</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format((float) $item->total_cantidad, 2) }}</td>
                <td style="text-align: right; font-weight: bold; color: #711C37;">${{ number_format((float) $item->total_costo, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center; color: #666; padding: 20px;">
                    No se registraron consumos/compras en el período seleccionado.
                </td>
            </tr>
        @endforelse
    </tbody>
    @if(count($items) > 0)
    <tfoot>
        <tr style="background: #f1f5f9;">
            <td colspan="2" style="text-align: right; font-weight: bold; text-transform: uppercase; padding: 10px;">Total General:</td>
            <td style="text-align: center; font-weight: bold; padding: 10px;">{{ number_format($totalCant, 2) }}</td>
            <td style="text-align: right; font-weight: bold; color: #711C37; font-size: 14px; padding: 10px;">${{ number_format($totalGral, 2) }}</td>
        </tr>
    </tfoot>
    @endif
</table>
