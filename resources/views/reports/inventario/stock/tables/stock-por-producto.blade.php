<table class="data-table stock-producto-table">
    <thead>
        <tr>
            <th class="col-producto">Producto</th>
            <th class="col-variante">Variante</th>
            <th class="col-categoria">Categoria</th>
            <th class="col-ubicacion">Ubicacion / Bodega</th>
            <th class="col-numero" style="text-align: right;">Stock Disponible</th>
            <th class="col-numero" style="text-align: right;">En Cuarentena</th>
            <th class="col-lotes" style="text-align: center;">Total Lotes</th>
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
                <td colspan="7" style="text-align: center; color: #718096; padding: 20px;">
                    No hay existencias de stock registradas.
                </td>
            </tr>
        @endforelse
    </tbody>
    @if(($esUltimaPagina ?? false) && count($items) > 0)
        <tfoot>
            <tr style="background:#f1f5f9;">
                <td colspan="4" style="text-align:right; font-weight:bold; text-transform:uppercase; padding:10px;">Total General:</td>
                <td style="text-align:right; font-weight:bold; color:#711C37; padding:10px;">{{ number_format($totalStock ?? 0, 2) }}</td>
                <td style="text-align:right; font-weight:bold; color:#d97706; padding:10px;">{{ number_format($totalCuarentena ?? 0, 2) }}</td>
                <td style="text-align:center; font-weight:bold; padding:10px;">{{ $totalRegistros ?? count($items) }} prod.</td>
            </tr>
        </tfoot>
    @endif
</table>
