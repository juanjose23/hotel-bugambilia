<table class="data-table">
    <thead>
        <tr>
            <th>Folio / Serie</th>
            <th>Fecha Emisión</th>
            <th>Receptor / Razón Social</th>
            <th class="amount" style="text-align: right;">Subtotal</th>
            <th class="amount" style="text-align: right;">IVA / Impuestos</th>
            <th class="amount" style="text-align: right;">Total Facturado</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $factura)
            <tr>
                <td><span class="sku-code">{{ $factura->folio ?? $factura->numero_factura ?? 'FAC-'.$factura->id }}</span></td>
                <td>{{ optional($factura->fecha_emision)->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $factura->razon_social ?? $factura->cliente?->persona?->nombre_completo ?? 'Cliente General' }}</td>
                <td class="amount" style="text-align: right;">$ {{ number_format((float) $factura->subtotal, 2) }}</td>
                <td class="amount" style="text-align: right;">$ {{ number_format((float) $factura->iva_total, 2) }}</td>
                <td class="amount positive" style="text-align: right; color: #047857; font-weight: bold;">$ {{ number_format((float) $factura->total, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty-row" style="text-align: center; color: #64748b; padding: 14px;">No hay facturas emitidas en el periodo seleccionado.</td>
            </tr>
        @endforelse
    </tbody>
    @if(($esUltimaPagina ?? false) && count($items) > 0)
        <tfoot>
            <tr style="background:#f1f5f9;">
                <td colspan="3" style="text-align:right; font-weight:bold; text-transform:uppercase; padding:10px;">Total General:</td>
                <td style="text-align:right; font-weight:bold; color:#711C37; padding:10px;">$ {{ number_format((float) ($totalSubtotal ?? 0), 2) }}</td>
                <td style="text-align:right; font-weight:bold; color:#711C37; padding:10px;">$ {{ number_format((float) ($totalImpuestos ?? 0), 2) }}</td>
                <td style="text-align:right; font-weight:bold; color:#047857; padding:10px;">$ {{ number_format((float) ($totalGeneral ?? 0), 2) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
