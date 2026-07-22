<table class="data-table">
    <thead>
        <tr>
            <th>Producto</th>
            <th style="text-align: right;">Total Salidas</th>
            <th style="text-align: right;">Stock Promedio</th>
            <th style="text-align: right;">Índice Rotación</th>
            <th style="text-align: center;">Clasificación</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $row)
            <tr>
                <td><strong>{{ $row->producto }}</strong></td>
                <td style="text-align: right;">{{ $row->totalSalidas }}</td>
                <td style="text-align: right;">{{ number_format($row->stockPromedio, 2) }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($row->indiceRotacion, 2) }}</td>
                <td style="text-align: center;">
                    @php
                        $color = match($row->clasificacion) {
                            'Alta' => 'background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7;',
                            'Media' => 'background: #fef3c7; color: #d97706; border: 1px solid #fde68a;',
                            default => 'background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;',
                        };
                    @endphp
                    <span class="badge" style="{{ $color }}">
                        {{ $row->clasificacion }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #718096; padding: 20px;">No hay datos de rotación suficientes.</td>
            </tr>
        @endforelse
    </tbody>
</table>
