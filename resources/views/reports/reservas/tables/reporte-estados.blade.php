@use(App\Enums\Reservas\EstadoReserva)
@use(App\Support\MonedaHelper)
<table class="data-table">
    <thead>
        <tr>
            <th>Código Reserva</th>
            <th>Titular</th>
            <th>Estado Actual</th>
            <th>Check-In</th>
            <th>Check-Out</th>
            <th class="amount" style="text-align: right;">Monto Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $reserva)
            <tr>
                <td><span class="sku-code">{{ $reserva->codigo_reserva }}</span></td>
                <td>{{ $reserva->cliente?->persona?->nombre_completo ?? 'N/A' }}</td>
                <td>
                    <span class="badge {{ $reserva->estado === EstadoReserva::CONFIRMADA || $reserva->estado === EstadoReserva::CHECKED_OUT ? 'badge-success' : ($reserva->estado === EstadoReserva::CANCELADA || $reserva->estado === EstadoReserva::NO_SHOW ? 'badge-danger' : 'badge-warning') }}">
                        {{ $reserva->estado?->label() ?? 'Pendiente' }}
                    </span>
                </td>
                <td>{{ optional($reserva->fecha_check_in)->format('d/m/Y') ?? '-' }}</td>
                <td>{{ optional($reserva->fecha_check_out)->format('d/m/Y') ?? '-' }}</td>
                <td class="amount" style="text-align: right;">{{ MonedaHelper::simbolo() }} {{ number_format((float) $reserva->total, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty-row" style="text-align: center; color: #64748b; padding: 14px;">No hay reservaciones para el estado seleccionado.</td>
            </tr>
        @endforelse
    </tbody>
</table>
