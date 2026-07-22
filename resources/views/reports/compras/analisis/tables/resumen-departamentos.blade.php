<table class="data-table">
    <thead>
        <tr>
            <th>Departamento</th>
            <th style="text-align: center;">Cant. Órdenes</th>
            <th style="text-align: right;">Total Ejecutado (OC)</th>
        </tr>
    </thead>
    <tbody>
        @php $totalGral = 0; @endphp
        @forelse($items as $item)
        @php $totalGral += $item->total_oc; @endphp
        <tr>
            <td><strong>{{ $item->departamento }}</strong></td>
            <td style="text-align: center;">{{ $item->conteo_ordenes }}</td>
            <td style="text-align: right; font-weight: bold; color: #711C37;">${{ number_format($item->total_oc, 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="3" style="text-align:center;color:#888;padding:20px;">Sin órdenes registradas.</td></tr>
        @endforelse
    </tbody>
    @if(count($items) > 0)
    <tfoot>
        <tr style="background: #f1f5f9;">
            <td colspan="2" style="text-align: right; font-weight: bold; text-transform: uppercase; padding: 10px;">Total General Ejecutado:</td>
            <td style="text-align: right; font-weight: bold; color: #711C37; font-size: 14px; padding: 10px;">${{ number_format($totalGeneral, 2) }}</td>
        </tr>
    </tfoot>
    @endif
</table>

@if(isset($i) && $i === 0 && count($items) > 0)
<div style="margin-top: 30px;">
    <strong style="font-size: 9px; color: #711C37; text-transform: uppercase; display: block; margin-bottom: 10px;">Distribución del Gasto</strong>
    @foreach($items as $item)
    @php $porcentaje = ($totalGeneral > 0) ? ($item->total_oc / $totalGeneral) * 100 : 0; @endphp
    <div style="margin-bottom: 8px;">
        <div style="font-size: 8px; margin-bottom: 2px;">{{ $item->departamento }} ({{ number_format($porcentaje, 1) }}%)</div>
        <div style="width: 100%; background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden;">
            <div style="width: {{ $porcentaje }}%; background: #711C37; height: 100%;"></div>
        </div>
    </div>
    @endforeach
</div>
@endif
