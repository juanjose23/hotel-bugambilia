<table class="data-table">
    <thead>
        <tr>
            <th style="width: 18%;">Código Reserva</th>
            <th style="width: 25%;">Titular / Cliente</th>
            <th style="width: 21%;">Habitación</th>
            <th class="amount" style="width: 12%; text-align: right;">Total</th>
            <th class="amount" style="width: 12%; text-align: right;">Pagado</th>
            <th class="amount" style="width: 12%; text-align: right;">Saldo</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $reserva)
            <tr>
                <td><span class="sku-code">{{ $reserva->codigo_reserva }}</span></td>
                <td>{{ $reserva->cliente?->persona?->nombre_completo ?? 'N/A' }}</td>
                <td>{{ $reserva->habitacion?->nombre ?? 'N/A' }}</td>
                <td class="amount" style="text-align: right;">$ {{ number_format((float) $reserva->total, 2) }}</td>
                <td class="amount positive" style="text-align: right; color: #047857; font-weight: bold;">$ {{ number_format((float) $reserva->total_pagado, 2) }}</td>
                <td class="amount danger" style="text-align: right; color: #b91c1c; font-weight: bold;">$ {{ number_format((float) $reserva->saldo, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty-row" style="text-align: center; color: #64748b; padding: 10px; font-size: 8pt;">No hay reservaciones con saldo pendiente en el periodo.</td>
            </tr>
        @endforelse
    </tbody>
</table>
