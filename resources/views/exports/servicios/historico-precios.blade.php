<table>
    <thead>
    <tr>
        <th colspan="8" style="font-size: 14px; font-weight: bold; text-align: center;">
            Hotel Bugambilias — Histórico de Servicios por Precio por Moneda
        </th>
    </tr>
    <tr>
        <th colspan="8" style="font-size: 10px; text-align: center;">
            Generado: {{ now()->format('d/m/Y H:i') }}
        </th>
    </tr>
    <tr><td colspan="8"></td></tr>
    <tr>
        <th style="font-weight: bold; background-color: #711C37; color: #ffffff;">Moneda</th>
        <th style="font-weight: bold; background-color: #711C37; color: #ffffff;">Código</th>
        <th style="font-weight: bold; background-color: #711C37; color: #ffffff;">Servicio</th>
        <th style="font-weight: bold; background-color: #711C37; color: #ffffff;">Precio</th>
        <th style="font-weight: bold; background-color: #711C37; color: #ffffff;">Vigencia Desde</th>
        <th style="font-weight: bold; background-color: #711C37; color: #ffffff;">Vigencia Hasta</th>
        <th style="font-weight: bold; background-color: #711C37; color: #ffffff;">Estado</th>
        <th style="font-weight: bold; background-color: #711C37; color: #ffffff;">Oferta</th>
    </tr>
    </thead>
    <tbody>
    @forelse($agrupado as $categoria => $items)
        <tr>
            <td colspan="8" style="font-weight: bold; background-color: #f1f5f9;">
                {{ $categoria }}
            </td>
        </tr>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->moneda_codigo ?: $item->moneda }}</td>
                <td>{{ $item->servicio_codigo }}</td>
                <td>{{ $item->servicio }}</td>
                <td style="text-align: right;">{{ number_format((float) $item->precio, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($item->fecha_inicio)->format('d/m/Y') }}</td>
                <td>{{ $item->fecha_fin ? \Carbon\Carbon::parse($item->fecha_fin)->format('d/m/Y') : '—' }}</td>
                <td>
                    @if((int) $item->estado === 1) Vigente
                    @else No Vigente
                    @endif
                </td>
                <td>{{ $item->es_oferta ? 'Sí' : 'No' }}</td>
            </tr>
        @endforeach
    @empty
        <tr>
            <td colspan="8" style="text-align: center; color: #999;">
                No se encontraron registros.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
