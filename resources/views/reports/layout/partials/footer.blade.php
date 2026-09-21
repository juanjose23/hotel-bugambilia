<div style="
    border-top: 1.5px solid #711C37;
    padding-top: 4px;
    width: 100%;
    font-family: Helvetica, Arial, sans-serif;
    font-size: 7.5pt;
    line-height: 1.35;
    color: #64748b;
">
    <div style="float:left; width:65%; white-space:nowrap; overflow:hidden;">
        <span style="color:#94a3b8;">Generado:</span>
        <strong style="color:#334155;">
            {{ $generadoEn ?? $fecha ?? ($datosHotel['generadoEn'] ?? null) ?? now()->format('d/m/Y H:i') }}
        </strong>
        &nbsp;|&nbsp;
        <span style="color:#94a3b8;">Por:</span>
        <strong style="color:#334155;">
            {{ $usuario ?? ($datosHotel['usuario'] ?? null) ?? auth()->user()?->name ?? 'Sistema' }}
        </strong>
        @if(isset($totalRegistros) && !is_null($totalRegistros))
            &nbsp;|&nbsp;
            <span style="color:#94a3b8;">Registros:</span>
            <strong style="color:#711C37;">{{ number_format((int) $totalRegistros) }}</strong>
        @endif
    </div>
    <div style="float:right; width:35%; text-align:right; font-weight:bold; color:#711C37; text-transform:uppercase;">
        {{ $nombreApp ?? config('app.name', 'HOTEL BUGAMBILIAS') }}
    </div>
    <div style="clear:both;"></div>
</div>