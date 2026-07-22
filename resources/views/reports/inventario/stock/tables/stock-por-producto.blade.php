<table class="data-table">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Variante</th>
            <th>Categoría</th>
            <th>Ubicación / Bodega</th>
            <th style="text-align: right;">Stock Disponible</th>
            <th style="text-align: right;">En Cuarentena</th>
            <th style="text-align: center;">Total Lotes</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $fila)
            <tr>
                <td><strong>{{ $fila->producto }}</strong></td>
                <td>{{ $fila->variante ?? 'Sin Variante / Base' }}</td>
                <td>{{ $fila->categoria ?? 'N/A' }}</td>
                <td>{{ $fila->ubicacion }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($fila->stockDisponible, 2) }}</td>
                <td style="text-align: right; color: #d97706;">{{ number_format($fila->stockCuarentena, 2) }}</td>
                <td style="text-align: center;">{{ $fila->totalLotes }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #718096; padding: 20px;">No hay existencias de stock registradas.</td>
            </tr>
        @endforelse
    </tbody>
</table>
