<table class="data-table">
    <thead>
        <tr>
            <th>Código de Lote</th>
            <th>Producto</th>
            <th>Variante</th>
            <th>Último Almacén</th>
            <th style="text-align: right;">Cantidad Inicial</th>
            <th style="text-align: right;">Merma Registrada</th>
            <th>Estado Final</th>
            <th>Fecha Registro</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $lote)
            <tr>
                <td><span style="font-family: monospace; font-weight: bold; color: #b91c1c;">{{ $lote->codigo_lote }}</span></td>
                <td><strong>{{ $lote->producto?->nombre }}</strong></td>
                <td>{{ $lote->variante?->nombre_variante ?? $lote->variante?->codigo ?? 'N/A' }}</td>
                <td>{{ $lote->ubicacion?->nombre ?? 'N/A' }}</td>
                <td style="text-align: right;">{{ number_format($lote->cantidad_inicial, 2) }}</td>
                <td style="text-align: right; font-weight: bold; color: #b91c1c;">
                    {{ number_format($lote->cantidad_inicial - $lote->cantidad_disponible, 2) }}
                </td>
                <td>
                    <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
                        {{ $lote->estado instanceof \App\Enums\Inventario\EstadoLote ? $lote->estado->label() : '' }}
                    </span>
                </td>
                <td>{{ $lote->updated_at?->format('d/m/Y H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #718096; padding: 20px;">No se registraron mermas en este período.</td>
            </tr>
        @endforelse
    </tbody>
    @if(($esUltimaPagina ?? false) && count($items) > 0)
        <tfoot>
            <tr style="background:#f1f5f9;">
                <td colspan="5" style="text-align:right; font-weight:bold; text-transform:uppercase; padding:10px;">Total General:</td>
                <td style="text-align:right; font-weight:bold; color:#711C37; padding:10px;">{{ number_format($items->sum(fn ($l) => $l->cantidad_inicial - $l->cantidad_disponible), 2) }}</td>
                <td colspan="2" style="text-align:right; font-weight:bold; color:#711C37; font-size:14px; padding:10px;">C$ {{ number_format($totalPerdida ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
