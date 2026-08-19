<table class="data-table">
    <thead>
        <tr>
            <th style="width: 20%;">Número Cuenta</th>
            <th style="width: 20%;">Tipo Cuenta</th>
            <th style="width: 24%;">Cliente / Titular</th>
            <th class="amount" style="width: 12%; text-align: right;">Total</th>
            <th class="amount" style="width: 12%; text-align: right;">Pagado</th>
            <th class="amount" style="width: 12%; text-align: right;">Saldo</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $cuenta)
            <tr>
                <td><span class="sku-code">{{ $cuenta->numero_cuenta }}</span></td>
                <td>{{ $cuenta->tipo_cuenta?->label() ?? 'General' }}</td>
                <td>{{ $cuenta->cliente?->persona?->nombre_completo ?? 'Cliente General' }}</td>
                <td class="amount" style="text-align: right;">$ {{ number_format((float) $cuenta->total, 2) }}</td>
                <td class="amount positive" style="text-align: right; color: #047857; font-weight: bold;">$ {{ number_format((float) $cuenta->total_pagado, 2) }}</td>
                <td class="amount danger" style="text-align: right; color: #b91c1c; font-weight: bold;">$ {{ number_format((float) $cuenta->saldo, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty-row" style="text-align: center; color: #64748b; padding: 10px; font-size: 8pt;">No hay folios o cuentas abiertas con saldo pendiente.</td>
            </tr>
        @endforelse
    </tbody>
</table>
