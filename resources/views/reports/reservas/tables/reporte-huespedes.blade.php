<table class="data-table">
    <thead>
        <tr>
            <th>Identificación / Cédula</th>
            <th>Nombre del Cliente Titular</th>
            <th>Correo Electrónico</th>
            <th>Teléfono Contacto</th>
            <th style="text-align: center;">Reservas Históricas</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $cliente)
            <tr>
                <td><span class="sku-code">{{ $cliente->persona?->numero_identificacion ?? 'N/D' }}</span></td>
                <td><strong>{{ $cliente->persona?->nombre_completo ?? 'Cliente N/D' }}</strong></td>
                <td>{{ $cliente->persona?->email ?? '-' }}</td>
                <td>{{ $cliente->persona?->telefono ?? '-' }}</td>
                <td style="text-align: center;">{{ $cliente->reservas_count ?? (isset($cliente->reservas) ? count($cliente->reservas) : 0) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="empty-row" style="text-align: center; color: #64748b; padding: 14px;">No hay directorio de huéspedes registrado.</td>
            </tr>
        @endforelse
    </tbody>
</table>
