<table>
    <thead>
    <tr>
        <th colspan="14" style="font-size: 16pt; font-weight: bold; color: #711C37;">HOTEL BUGAMBILIAS - EXPORTACIÓN DE PRODUCTOS</th>
    </tr>
    <tr>
        <th colspan="14" style="color: #4a5568;">CÓDIGO DE REPORTE: HTB-CP-004 | FECHA: {{ $fecha }}</th>
    </tr>
    <tr><th colspan="14"></th></tr>
    <tr>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">ID PRODUCTO</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">TIPO</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">NOMBRE PRODUCTO</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">DESCRIPCIÓN</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">CATEGORÍA</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">MARCA</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">ESTADO PROD</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">ID VARIANTE</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">SKU</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">NOMBRE VARIANTE</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">ATRIBUTOS</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">PESO (g)</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">VOLUMEN (ml)</th>
        <th style="background-color: #711C37; color: #ffffff; font-weight: bold;">ESTADO VAR</th>
    </tr>
    </thead>
    <tbody>
    @foreach($productos as $producto)
        @if($producto->variantes->isEmpty())
            <tr>
                <td>{{ $producto->id }}</td>
                <td>{{ $producto->tipo == 1 ? 'Perecedero' : 'No Perecedero' }}</td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->descripcion }}</td>
                <td>{{ $producto->categoria->nombre ?? 'N/A' }}</td>
                <td>{{ $producto->marca->nombre ?? 'N/A' }}</td>
                <td>{{ $producto->estado ? 'Activo' : 'Inactivo' }}</td>
                <td colspan="7">Sin variantes</td>
            </tr>
        @else
            @foreach($producto->variantes as $variante)
                <tr>
                    <td>{{ $producto->id }}</td>
                    <td>{{ $producto->tipo == 1 ? 'Perecedero' : 'No Perecedero' }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->descripcion }}</td>
                    <td>{{ $producto->categoria->nombre ?? 'N/A' }}</td>
                    <td>{{ $producto->marca->nombre ?? 'N/A' }}</td>
                    <td>{{ $producto->estado ? 'Activo' : 'Inactivo' }}</td>
                    <td>{{ $variante->id }}</td>
                    <td>{{ $variante->codigo }}</td>
                    <td>{{ $variante->nombre_variante }}</td>
                    <td>{{ is_array($variante->atributos) ? json_encode($variante->atributos) : $variante->atributos }}</td>
                    <td>{{ $variante->peso }}</td>
                    <td>{{ $variante->volumen }}</td>
                    <td>{{ $variante->estado ? 'Activo' : 'Inactivo' }}</td>
                </tr>
            @endforeach
        @endif
    @endforeach
    </tbody>
</table>
