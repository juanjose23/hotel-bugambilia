<table class="data-table">
    <thead>
        <tr>
            <th>Código Inventario</th>
            <th>Producto</th>
            <th>Proveedor</th>
            <th style="text-align: right;">Costo Adquisición</th>
            <th style="text-align: center;">Fin Garantía</th>
            <th style="text-align: center;">Días Restantes</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $activo)
            @php
                $diasRestantes = $activo->fecha_garantia_fin
                    ? (int) now()->diffInDays($activo->fecha_garantia_fin, false)
                    : null;
                $diasColor = $diasRestantes !== null && $diasRestantes <= 15 ? '#991b1b' : '#92400e';
                $diasBg = $diasRestantes !== null && $diasRestantes <= 15 ? '#fee2e2' : '#fef3c7';
            @endphp
            <tr>
                <td>@include('reports.activos.partials.sku', ['codigo' => $activo->codigo_inventario])</td>
                <td><strong>{{ $activo->producto?->nombre ?? '—' }}</strong></td>
                <td style="font-size:9px;">{{ $activo->proveedor?->persona?->primer_nombre ?? '—' }}</td>
                <td style="text-align: right;">
                    @include('reports.activos.partials.costo', ['monto' => $activo->costo_adquisicion, 'monedaSimbolo' => $activo->moneda?->simbolo])
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ $activo->fecha_garantia_fin?->format('d/m/Y') ?? '—' }}
                </td>
                <td style="text-align: center;">
                    @if($diasRestantes !== null)
                        <span class="badge" style="background:{{ $diasBg }};color:{{ $diasColor }};border:1px solid {{ $diasColor }};">
                            {{ $diasRestantes }} días
                        </span>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @empty
            @include('reports.activos.partials.empty-state', ['colspan' => 6, 'mensaje' => 'No hay activos con garantía próxima a vencer en el período seleccionado.'])
        @endforelse
    </tbody>
</table>
