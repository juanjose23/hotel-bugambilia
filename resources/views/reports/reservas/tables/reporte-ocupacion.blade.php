@use(App\Support\MonedaHelper)
<table class="data-table">
    <thead>
        <tr>
            <th>Código Reserva</th>
            <th>Habitación</th>
            <th>Check-In</th>
            <th>Check-Out</th>
            <th style="text-align: center;">Noches</th>
            <th class="amount" style="text-align: right;">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $reserva)
            <tr>
                <td><span class="sku-code">{{ $reserva->codigo_reserva }}</span></td>
                <td>{{ $reserva->habitacion?->nombre ?? 'N/A' }}</td>
                <td>{{ optional($reserva->fecha_check_in)->format('d/m/Y') ?? '-' }}</td>
                <td>{{ optional($reserva->fecha_check_out)->format('d/m/Y') ?? '-' }}</td>
                <td style="text-align: center;">{{ $reserva->noches ?? 1 }}</td>
                <td class="amount" style="text-align: right;">{{ MonedaHelper::simbolo() }} {{ number_format((float) $reserva->total, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty-row" style="text-align: center; color: #64748b; padding: 14px;">No hay estadías registradas para el periodo especificado.</td>
            </tr>
        @endforelse
    </tbody>
    @if(($esUltimaPagina ?? false) && count($items) > 0)
        <tfoot>
            <tr style="background:#f1f5f9;">
                <td colspan="4" style="text-align:right; font-weight:bold; text-transform:uppercase; padding:10px;">Total General:</td>
                <td style="text-align:center; font-weight:bold; padding:10px;">{{ $totalNoches ?? 0 }}</td>
                <td style="text-align:right; font-weight:bold; color:#711C37; padding:10px;">{{ MonedaHelper::simbolo() }} {{ number_format((float) ($totalIngresos ?? 0), 2) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
