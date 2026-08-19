<table style="width:100%; border-collapse:collapse; border:none; margin-bottom: 2mm;">
    <tr>
        <td style="vertical-align:middle; width:40%; border:none; padding:0;">
            @if (!empty($logo_base64))
                <img src="{{ $logo_base64 }}" alt="Logo" style="max-height:60px; max-width:140px; display:block;">
            @else
                <div style="font-size:14pt; font-weight:bold; color:#711C37; text-transform:uppercase;">HOTEL BUGAMBILIAS</div>
            @endif
        </td>
        <td style="text-align:right; vertical-align:middle; width:60%; border:none; padding:0;">
            <div style="font-size:13pt; font-weight:bold; color:#711C37; text-transform:uppercase; line-height:1.2;">
                {{ $nombreReporte ?? $titulo ?? 'Reporte Oficial' }}
            </div>
            @if(!empty($codigoReporte ?? $codigo))
                <div style="font-size:9pt; font-weight:bold; color:#711C37; font-family:'Courier New',monospace; margin-top:2px;">
                    {{ $codigoReporte ?? $codigo }}
                </div>
            @endif
            <div style="font-size:8pt; color:#475569; margin-top:2px; line-height:1.3;">
                {{ $hotelInfo['direccion'] ?? 'Salida Sur Estelí, Restaurante Absoluto 1c. Oeste, 2c. Sur, 1c. Oeste' }}<br>
                Tel: {{ $hotelInfo['telefono'] ?? '+505 8713 6805' }} | {{ $hotelInfo['email'] ?? 'recepcion@bugambiliashotel.com' }}
            </div>
        </td>
    </tr>
</table>
<div style="border-bottom: 2px solid #711C37; width: 100%; margin-top: 1mm; margin-bottom: 3mm;"></div>
