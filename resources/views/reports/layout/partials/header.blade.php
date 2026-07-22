<table style="width:100%;border-collapse:collapse;border:none;">
    <tr>
        <td style="vertical-align:middle;width:50%;border:none;padding:0;">
            @if (!empty($logo_base64))
                <img src="{{ $logo_base64 }}" alt="Logo"
                     style="max-height:72px;max-width:100px;display:block;">
            @endif
        </td>
        <td style="text-align:right;vertical-align:middle;width:70%;border:none;padding:0;">
            <div style="font-size:15px;font-weight:bold;color:#711C37;text-transform:uppercase;line-height:1.2;">
                {{ $nombreReporte ?? 'Reporte' }}
            </div>
            <div style="font-size:10px;font-weight:bold;color:#555;font-family:'Courier New',monospace;margin-top:2px;">
                {{ $codigoReporte ?? '' }}
            </div>
            <div style="font-size:8px;color:black;margin-top:2px;line-height:1.3;">
                {{ $hotelInfo['direccion'] ?? '' }}<br>
                Tel: {{ $hotelInfo['telefono'] ?? '' }} | {{ $hotelInfo['email'] ?? '' }}
            </div>
        </td>
    </tr>
</table>
