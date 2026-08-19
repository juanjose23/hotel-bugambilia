@php
    $cols = $campos ?? [];
    $colCount = count($cols);
    $colWidth = $colCount > 0 ? round(100 / $colCount, 0) : 33;
@endphp
<div style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px 15px;border-radius:4px;{{ $wrapperStyle ?? 'margin-bottom:16px;' }}">
    <table style="width:100%;border-collapse:collapse;">
        @for($row = 0; $row < $colCount; $row += 3)
            <tr>
                @for($col = 0; $col < 3; $col++)
                    @php $idx = $row + $col; @endphp
                    @if($idx < $colCount)
                        @php $campo = $cols[$idx]; @endphp
                        <td style="width:{{ $colWidth }}%;border:none;padding:4px 0;font-size:9px;">
                            <strong style="color:#711C37;text-transform:uppercase;">{{ $campo['label'] }}:</strong><br>
                            @if(isset($campo['isCosto']) && $campo['isCosto'])
                                <span style="font-size:12px;font-weight:bold;color:#711C37;">
                                    {{ $campo['monedaSimbolo'] ?? '$' }}{{ number_format((float) ($campo['value'] ?? 0), 2) }}
                                </span>
                            @else
                                <span style="font-size:11px;">{{ $campo['value'] ?? '—' }}</span>
                            @endif
                        </td>
                    @else
                        <td style="border:none;padding:0;"></td>
                    @endif
                @endfor
            </tr>
        @endfor
    </table>
</div>
