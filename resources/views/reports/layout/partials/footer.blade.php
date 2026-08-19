<div style="border-top: 1px solid #cbd5e1; padding-top: 4px; width: 100%;">
    <table style="width:100%; border-collapse:collapse; border:none;">
        <tr>
            <td style="text-align:left; width:65%; border:none; padding:0; font-size:7.5pt; color:#64748b; text-transform:uppercase;">
                Generado en: {{ $generadoEn ?? $fecha ?? now()->format('d/m/Y H:i') }} &nbsp;|&nbsp;
                Generado por: {{ $usuario ?? auth()->user()?->name ?? 'Sistema' }}
                @isset($totalRegistros)
                    &nbsp;|&nbsp; <strong>Total Registros:</strong> {{ $totalRegistros }}
                @endisset
            </td>
            <td style="text-align:right; width:35%; border:none; padding:0; font-weight:bold; color:#711C37; text-transform:uppercase; font-size:8.5pt;">
                {{ config('app.name', 'HOTEL BUGAMBILIAS') }}
            </td>
        </tr>
    </table>
</div>
