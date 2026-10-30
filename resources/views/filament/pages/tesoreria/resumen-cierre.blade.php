<div data-guia="caja-saldos" class="kitchen-shell">
    @if($sesion)
        <div class="kitchen-intro"><div><h2>{{ $sesion->caja->nombre }} · {{ $sesion->usuario->name }}</h2><p>Apertura {{ $sesion->abierta_at->format('d/m/Y H:i') }} · {{ match($sesion->estado){'abierta'=>'Turno abierto','pendiente_revision'=>'Pendiente de autorización','cerrada'=>'Turno cerrado',default=>$sesion->estado} }}</p></div></div>
        <div class="kitchen-panel"><div class="kitchen-table-wrap"><table class="kitchen-table"><thead><tr><th>Moneda</th><th>Fondo recibido</th><th>Esperado</th><th>Contado</th><th>Diferencia</th></tr></thead><tbody>
            @foreach($saldos as $fila)<tr><td data-label="Moneda">{{ $fila['moneda'] }}</td><td data-label="Fondo">{{ number_format($fila['apertura'],2,',','.') }}</td><td data-label="Esperado">{{ number_format($fila['esperado'],2,',','.') }}</td><td data-label="Contado">{{ $fila['contado']!==null?number_format($fila['contado'],2,',','.'):'Por contar' }}</td><td data-label="Diferencia">@if($fila['diferencia']===null)Por contar @elseif($fila['diferencia']<0)<span class="kitchen-status kitchen-status-danger">Faltante {{ number_format(abs($fila['diferencia']),2,',','.') }}</span>@elseif($fila['diferencia']>0)<span class="kitchen-status kitchen-status-warning">Sobrante {{ number_format($fila['diferencia'],2,',','.') }}</span>@else<span class="kitchen-status">Cuadra</span>@endif</td></tr>@endforeach
        </tbody></table></div></div>
        <p class="kitchen-review-note"><strong>Origen del fondo:</strong> {{ $sesion->origen_fondo }}</p>
        @if($sesion->motivo_diferencia)<div class="kitchen-callout kitchen-callout-danger"><strong>Explicación de la diferencia:</strong> {{ $sesion->motivo_diferencia }}</div>@endif
        @if($sesion->estado==='pendiente_revision')<div class="kitchen-callout">El turno no admite nuevos movimientos. Otro usuario con permiso debe revisar el arqueo en Gestión de cajas.</div>@endif
        @if($sesion->estado==='cerrada')<div class="kitchen-callout">Cierre conservado desde {{ $sesion->cerrada_at->format('d/m/Y H:i') }}. El arqueo y sus movimientos quedan para consulta.</div>@endif
    @endif
</div>
