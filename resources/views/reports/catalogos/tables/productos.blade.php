<table class="data-table">
    <thead>
        <tr>
            <th class="col-sku">Codigo (SKU)</th>
            <th class="col-nombre">Nombre de Producto</th>
            <th class="col-desc">Descripcion</th>
        </tr>
    </thead>
    <tbody>
        @if($incluirVariantes ?? false)
            @forelse($items as $producto)
                <tr>
                    <td class="sku-code">{{ $producto->codigo ?? '#' . $producto->id }}</td>
                    <td>
                        <strong>{{ $producto->nombre }}</strong>
                        @if(optional($producto->variantes)->isNotEmpty())
                            <span class="badge-count">({{ $producto->variantes->count() }} var.)</span>
                        @endif
                    </td>
                    <td>{{ $producto->descripcion }}</td>
                </tr>
                @foreach($producto->variantes ?? [] as $v)
                    <tr class="row-variant">
                        <td class="sku-code" style="color: #64748b;">{{ $v->codigo }}</td>
                        <td>- {{ $v->nombre_variante ?? 'Estandar' }}</td>
                        <td style="color: #64748b;">{{ $v->descripcion ?? '' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="3" style="text-align:center; color:#64748b; padding:16px;">
                        No hay productos en el catalogo.
                    </td>
                </tr>
            @endforelse
        @else
            @forelse($items as $producto)
                <tr>
                    <td class="sku-code">{{ $producto->codigo ?? '#' . $producto->id }}</td>
                    <td><strong>{{ $producto->nombre }}</strong></td>
                    <td>{{ $producto->descripcion }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center; color:#64748b; padding:16px;">
                        No hay productos en el catalogo.
                    </td>
                </tr>
            @endforelse
        @endif
    </tbody>
</table>
