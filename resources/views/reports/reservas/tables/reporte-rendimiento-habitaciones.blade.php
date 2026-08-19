<table class="data-table">
    <thead>
        <tr>
            <th>Categoría de Habitación</th>
            <th style="text-align: center;">Capacidad Personas</th>
            <th class="amount" style="text-align: right;">Tarifa Noche Base</th>
            <th style="text-align: center;">Habitaciones Activas</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $categoria)
            @php $cat = is_array($categoria) ? (object) $categoria : $categoria; @endphp
            <tr>
                <td><strong>{{ $cat->nombre ?? 'N/D' }}</strong></td>
                <td style="text-align: center;">{{ $cat->capacidad_total ?? 0 }} pers.</td>
                <td class="amount" style="text-align: right;">$ {{ number_format((float) ($cat->precio_base_noche ?? 0), 2) }}</td>
                <td style="text-align: center;">{{ $cat->habitaciones_activas ?? 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="empty-row" style="text-align: center; color: #64748b; padding: 14px;">No hay categorías registradas en el catálogo.</td>
            </tr>
        @endforelse
    </tbody>
</table>
