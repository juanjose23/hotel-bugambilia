<table class="data-table">
    <thead>
        <tr>
            <th>Proveedor</th>
            <th style="text-align: center;">Órdenes Completadas</th>
            <th style="text-align: center;">Promedio Días de Entrega (Lead Time)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
            <tr>
                <td><strong>{{ $item->proveedor_nombre }}</strong></td>
                <td style="text-align: center;">{{ $item->ordenes_recibidas }}</td>
                <td style="text-align: center; font-weight: bold; color: #711C37;">
                    {{ number_format((float) $item->promedio_dias, 1) }} días
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #64748b; padding: 16px;">
                    No se registraron recepciones físicas en el período seleccionado.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
