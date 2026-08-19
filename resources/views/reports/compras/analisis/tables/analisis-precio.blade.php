@php use Carbon\Carbon; @endphp

@forelse($items as $producto)
    <div class="avoid-break" style="margin-bottom: 12px; border: 1px solid #b8c4d4; border-radius: 4px; overflow: hidden; background: #ffffff;">
        <div style="background: #f8fafc; border-bottom: 1px solid #b8c4d4; padding: 6px 10px;">
            <table style="width: 100%; margin: 0; border-collapse: collapse;">
                <tr>
                    <td style="border: none; padding: 0; text-align: left; vertical-align: middle;">
                        <span style="font-size: 9pt; font-weight: bold; color: #711C37; text-transform: uppercase;">
                            {{ $producto->producto_nombre }}
                        </span>
                    </td>
                    <td style="border: none; padding: 0; text-align: right; vertical-align: middle; font-size: 7.5pt; color: #475569;">
                        <span style="display: inline-block; background: #e2e8f0; padding: 2px 6px; border-radius: 3px; margin-left: 4px;">
                            Mín: <strong>${{ number_format((float) $producto->precio_min, 2) }}</strong>
                        </span>
                        <span style="display: inline-block; background: #e2e8f0; padding: 2px 6px; border-radius: 3px; margin-left: 4px;">
                            Máx: <strong>${{ number_format((float) $producto->precio_max, 2) }}</strong>
                        </span>
                        <span style="display: inline-block; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 2px 6px; border-radius: 3px; font-weight: bold; margin-left: 4px;">
                            Promedio: ${{ number_format((float) $producto->precio_promedio, 2) }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <table class="data-table" style="margin: 0; border: none;">
            <thead>
                <tr>
                    <th style="font-size: 8pt; padding: 5px 8px;">Proveedor</th>
                    <th style="text-align: center; font-size: 8pt; padding: 5px 8px; width: 85px;">Fecha Compra</th>
                    <th style="text-align: right; font-size: 8pt; padding: 5px 8px; width: 95px;">Precio Unit.</th>
                    <th style="text-align: right; font-size: 8pt; padding: 5px 8px; width: 75px;">Cantidad</th>
                    <th style="text-align: right; font-size: 8pt; padding: 5px 8px; width: 100px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($producto->entradas as $entrada)
                    @php
                        $pu = (float) $entrada->precio_unitario;
                        $cant = (float) $entrada->cantidad;
                        $sub = $pu * $cant;
                        $esMin = abs($pu - (float) $producto->precio_min) < 0.001;
                        $esMax = abs($pu - (float) $producto->precio_max) < 0.001 && $producto->precio_min != $producto->precio_max;
                    @endphp
                    <tr>
                        <td style="font-size: 8pt; padding: 4px 8px;">{{ $entrada->proveedor ?? '—' }}</td>
                        <td style="text-align: center; font-size: 8pt; padding: 4px 8px;">
                            {{ !empty($entrada->fecha) ? Carbon::parse($entrada->fecha)->format('d/m/Y') : '—' }}
                        </td>
                        <td style="text-align: right; font-size: 8pt; padding: 4px 8px; font-weight: bold; color: {{ $esMin ? '#047857' : ($esMax ? '#b91c1c' : '#1e293b') }};">
                            ${{ number_format($pu, 2) }}
                            @if($esMin)
                                <span style="font-size: 6.5pt; color: #047857;">(MÍN)</span>
                            @elseif($esMax)
                                <span style="font-size: 6.5pt; color: #b91c1c;">(MÁX)</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-size: 8pt; padding: 4px 8px;">{{ number_format($cant, 2) }}</td>
                        <td style="text-align: right; font-size: 8pt; padding: 4px 8px; font-weight: bold; color: #711C37;">${{ number_format($sub, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b; padding: 10px; font-size: 8pt;">Sin registros de compra para este artículo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@empty
    <div style="text-align: center; color: #64748b; padding: 24px; font-size: 8.5pt;">
        Sin datos de compras en el período seleccionado.
    </div>
@endforelse

<div style="margin-top: 10px; font-size: 7.5pt; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px 10px; border-radius: 4px;" class="avoid-break">
    <strong>Nota:</strong> Los precios unitarios mínimos registrados se resaltan en verde y los máximos en rojo para facilitar la identificación de fluctuaciones de costos por producto.
</div>
