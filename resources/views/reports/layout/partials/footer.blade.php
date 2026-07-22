<table style="width:100%;border-collapse:collapse;border:none;">
    <tr>
        <td style="text-align:left;width:60%;border:none;padding:0;font-size:8px;color:black;text-transform:uppercase;">
            Generado en: {{ $generadoEn ?? $fecha ?? now()->format('d/m/Y H:i') }} &nbsp;|&nbsp;
            Generado por: {{ $usuario }}
            @isset($totalRegistros)
                &nbsp;|&nbsp; <strong>Total de registros:</strong> {{ $totalRegistros }}
            @endisset
        </td>
        <td style="text-align:right;width:40%;border:none;padding:0;font-weight:bold;color:black;text-transform:uppercase;font-size:9px;">
            {{ config('app.name') }}
        </td>
    </tr>
</table>
