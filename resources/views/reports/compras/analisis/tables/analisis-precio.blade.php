@php use Carbon\Carbon; @endphp
@forelse($items as $producto)
    <div style="margin-bottom:18px;">
        <div
            style="background:#711C37;color:white;padding:5px 10px;font-size:14px;font-weight:bold;border-radius:3px 3px 0 0;">{{ $producto->producto_nombre }}</div>
        <table class="data-table" style="margin-top:0;border-top:none;">
            <thead>
            <tr style="background:#f3f0f0;">
                <th>Proveedor</th>
                <th style="text-align:center;">Fecha Compra</th>
                <th style="text-align:right;">Precio Unitario</th>
                <th style="text-align:right;">Cantidad</th>
            </tr>
            </thead>
            <tbody>
            @foreach($producto->entradas as $entrada)
                <tr>
                    <td>{{ $entrada->proveedor }}</td>
                    <td style="text-align:center;">{{ Carbon::parse($entrada->fecha)->format('d/m/Y') }}</td>
                    <td style="text-align:right;font-weight:bold;">
                        ${{ number_format((float)$entrada->precio_unitario, 2) }}</td>
                    <td style="text-align:right;">{{ number_format((float)$entrada->cantidad, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr style="background:#f8fafc;">
                <td colspan="2" style="font-size:9px;color:#555;padding:5px 8px;">
                    Mín: <strong>${{ number_format((float)$producto->precio_min, 2) }}</strong>
                    &nbsp;|&nbsp; Máx:
                    <strong>${{ number_format((float)$producto->precio_max, 2) }}</strong>
                </td>
                <td style="text-align:right;color:#711C37;font-weight:bold;padding:5px 8px;">Prom:
                    ${{ number_format((float)$producto->precio_promedio, 2) }}</td>
                <td></td>
            </tr>
            </tfoot>
        </table>
    </div>
@empty
    <div style="text-align:center;color:#888;padding:30px;">Sin datos de compras en el período
        seleccionado.
    </div>
@endforelse
