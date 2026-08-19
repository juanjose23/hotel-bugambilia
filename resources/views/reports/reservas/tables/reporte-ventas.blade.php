<table class="data-table">
    <thead>
        <tr>
            <th>Reserva</th>
            <th>Titular</th>
            <th>Fecha Emisión</th>
            <th class="amount" style="text-align: right;">Venta Total</th>
            <th class="amount" style="text-align: right;">Pagado</th>
            <th class="amount" style="text-align: right;">Saldo</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $reserva)
            <tr>
                <td><span class="sku-code">{{ $reserva->codigo_reserva }}</span></td>
                <td>{{ $reserva->cliente?->persona?->nombre_completo ?? 'N/A' }}</td>
                <td>{{ optional($reserva->created_at)->format('d/m/Y') ?? '-' }}</td>
                <td class="amount" style="text-align: right;">$ {{ number_format((float) $reserva->total, 2) }}</td>
                <td class="amount positive" style="text-align: right; color: #047857; font-weight: bold;">$ {{ number_format((float) $reserva->total_pagado, 2) }}</td>
                <td class="amount {{ $reserva->saldo > 0 ? 'danger' : '' }}" style="text-align: right; {{ $reserva->saldo > 0 ? 'color: #b91c1c; font-weight: bold;' : '' }}">$ {{ number_format((float) $reserva->saldo, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty-row" style="text-align: center; color: #64748b; padding: 14px;">No hay registros de ventas en el periodo seleccionado.</td>
            </tr>
        @endforelse
    </tbody>
    @if(($esUltimaPagina ?? false) && count($items) > 0)
        <tfoot>
            <tr style="background:#f1f5f9;">
                <td colspan="3" style="text-align:right; font-weight:bold; text-transform:uppercase; padding:10px;">Total General:</td>
                <td style="text-align:right; font-weight:bold; color:#711C37; padding:10px;">$ {{ number_format((float) ($totalVentas ?? 0), 2) }}</td>
                <td style="text-align:right; font-weight:bold; color:#047857; padding:10px;">$ {{ number_format((float) ($totalPagado ?? 0), 2) }}</td>
                <td style="text-align:right; font-weight:bold; color:#b91c1c; padding:10px;">$ {{ number_format((float) ($totalSaldo ?? 0), 2) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
