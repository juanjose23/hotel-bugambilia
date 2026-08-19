<tfoot>
    <tr style="background:#f1f5f9;">
        <td colspan="{{ $labelColspan ?? 4 }}" style="text-align:right;font-weight:bold;text-transform:uppercase;padding:10px;">
            {{ $label ?? 'Total General:' }}
        </td>
        <td style="text-align:right;font-weight:bold;color:#711C37;font-size:14px;padding:10px;">
            {{ $monedaSimbolo ?? 'C$' }} {{ number_format((float) ($total ?? 0), 2) }}
        </td>
        @isset($count)
            <td style="text-align:center;font-weight:bold;padding:10px;">{{ $count }}</td>
        @endisset
    </tr>
</tfoot>
