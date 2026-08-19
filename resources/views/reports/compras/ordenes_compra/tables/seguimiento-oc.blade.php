<table class="data-table">
    <thead>
        <tr>
            <th>Código OC</th>
            <th>Proveedor</th>
            <th>Departamento</th>
            <th style="text-align:center;">Fecha Orden</th>
            <th style="text-align:center;">Entrega Est.</th>
            <th style="text-align:center;">Estado</th>
            <th style="text-align:center;">Recepciones</th>
            <th style="text-align:right;">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $row)
            @php
                $recepcionesCount = intval($row->total_recepciones ?? $row->recepcion_count ?? 0);
                $sinRecepcion = $recepcionesCount === 0;
                $montoTotal = floatval($row->total ?? $row->monto_total ?? 0);
                $estadoRaw = $row->estado_label ?? $row->estado ?? '-';
                $estado = is_numeric($estadoRaw)
                    ? (\App\Enums\Compras\EstadoOrdenCompra::tryFrom((int) $estadoRaw)?->getLabel() ?? (string) $estadoRaw)
                    : (is_object($estadoRaw) && method_exists($estadoRaw, 'getLabel')
                        ? $estadoRaw->getLabel()
                        : (is_object($estadoRaw) && method_exists($estadoRaw, 'label')
                            ? $estadoRaw->label()
                            : (string) $estadoRaw));
            @endphp
            <tr style="{{ $sinRecepcion ? 'background:#fff7ed;' : '' }}">
                <td><span class="sku-code">{{ $row->codigo ?? '-' }}</span></td>
                <td>{{ $row->proveedor_nombre ?? $row->proveedor ?? '—' }}</td>
                <td style="font-size:8pt;">{{ $row->departamento ?? '—' }}</td>
                <td style="text-align:center;">{{ isset($row->fecha_orden) ? \Carbon\Carbon::parse($row->fecha_orden)->format('d/m/Y') : '—' }}</td>
                <td style="text-align:center; {{ !empty($row->fecha_entrega_estimada) && \Carbon\Carbon::parse($row->fecha_entrega_estimada)->isPast() && $sinRecepcion ? 'color:#b91c1c; font-weight:bold;' : '' }}">
                    {{ !empty($row->fecha_entrega_estimada) ? \Carbon\Carbon::parse($row->fecha_entrega_estimada)->format('d/m/Y') : '—' }}
                </td>
                <td style="text-align:center;"><span class="badge badge-info">{{ $estado }}</span></td>
                <td style="text-align:center; font-weight:bold; color:{{ $sinRecepcion ? '#b91c1c' : '#047857' }};">{{ $recepcionesCount }}</td>
                <td style="text-align:right; font-weight:bold; color:#711C37;">${{ number_format($montoTotal, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#64748b; padding:16px;">Sin órdenes en el período.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr style="background:#f1f5f9;">
            <td colspan="6" style="text-align:right;font-weight:bold;text-transform:uppercase;padding:8px;">Total Órdenes:</td>
            <td style="text-align:center;font-weight:bold;padding:8px;">{{ count($items) }}</td>
            <td style="text-align:right;font-weight:bold;color:#711C37;font-size:11px;padding:8px;">
                ${{ number_format(collect($items)->sum(fn($r) => floatval($r->total ?? $r->monto_total ?? 0)), 2) }}
            </td>
        </tr>
    </tfoot>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#92400e; background:#fffbeb; border:1px solid #fde68a; padding:6px 10px; border-radius:4px;" class="avoid-break">
    Las filas en naranja claro indican órdenes <strong>sin recepciones registradas</strong>. Las fechas de entrega en rojo están <strong>vencidas</strong>.
</div>
