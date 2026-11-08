<div data-guia="caja-saldos" class="kitchen-shell space-y-4">
    @if($sesion)
        @php
            $duracion = $sesion->cerrada_at
                ? $sesion->abierta_at->diffForHumans($sesion->cerrada_at, \Carbon\CarbonInterface::DIFF_ABSOLUTE)
                : 'Abierto ' . $sesion->abierta_at->diffForHumans();
            $movimientos = $sesion->movimientos()->with(['moneda', 'usuario'])->orderByDesc('id')->get();
        @endphp

        <div class="kitchen-intro">
            <div>
                <h2>{{ $sesion->caja->nombre }} · {{ $sesion->usuario->name }}</h2>
                <p>
                    <strong>Apertura:</strong> {{ $sesion->abierta_at->format('d/m/Y H:i') }}
                    @if($sesion->cerrada_at)
                        · <strong>Cierre:</strong> {{ $sesion->cerrada_at->format('d/m/Y H:i') }}
                    @endif
                    · <strong>Tiempo:</strong> {{ $duracion }}
                    · <span>{{ match($sesion->estado){'abierta'=>'Turno abierto','pendiente_revision'=>'Pendiente de autorización','cerrada'=>'Turno cerrado',default=>$sesion->estado} }}</span>
                </p>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold mb-2">Arqueo por moneda</h3>
            <div class="kitchen-panel">
                <div class="kitchen-table-wrap">
                    <table class="kitchen-table">
                        <thead>
                            <tr>
                                <th>Moneda</th>
                                <th>Fondo recibido</th>
                                <th>Esperado</th>
                                <th>Contado</th>
                                <th>Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($saldos as $fila)
                                <tr>
                                    <td data-label="Moneda">{{ $fila['moneda'] }}</td>
                                    <td data-label="Fondo">{{ number_format($fila['apertura'],2,',','.') }}</td>
                                    <td data-label="Esperado">{{ number_format($fila['esperado'],2,',','.') }}</td>
                                    <td data-label="Contado">{{ $fila['contado']!==null?number_format($fila['contado'],2,',','.'):'Por contar' }}</td>
                                    <td data-label="Diferencia">
                                        @if($fila['diferencia']===null)
                                            Por contar
                                        @elseif($fila['diferencia']<0)
                                            <span class="kitchen-status kitchen-status-danger">Faltante {{ number_format(abs($fila['diferencia']),2,',','.') }}</span>
                                        @elseif($fila['diferencia']>0)
                                            <span class="kitchen-status kitchen-status-warning">Sobrante {{ number_format($fila['diferencia'],2,',','.') }}</span>
                                        @else
                                            <span class="kitchen-status">Cuadra</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <p class="kitchen-review-note"><strong>Origen del fondo:</strong> {{ $sesion->origen_fondo }}</p>
        @if($sesion->motivo_diferencia)
            <div class="kitchen-callout kitchen-callout-danger"><strong>Explicación de la diferencia:</strong> {{ $sesion->motivo_diferencia }}</div>
        @endif
        @if($sesion->estado==='pendiente_revision')
            <div class="kitchen-callout">El turno no admite nuevos movimientos. Otro usuario con permiso debe revisar el arqueo en Gestión de cajas.</div>
        @endif
        @if($sesion->estado==='cerrada')
            <div class="kitchen-callout">Cierre conservado desde {{ $sesion->cerrada_at->format('d/m/Y H:i') }}. El arqueo y sus movimientos quedan para consulta.</div>
        @endif

        <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold mb-2">Movimientos del turno ({{ $movimientos->count() }})</h3>
            @if($movimientos->isEmpty())
                <p class="text-xs text-gray-500 italic">No se han registrado cobros o egresos durante este turno.</p>
            @else
                <div class="kitchen-panel max-h-72 overflow-y-auto">
                    <div class="kitchen-table-wrap">
                        <table class="kitchen-table text-xs">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Tipo</th>
                                    <th>Concepto</th>
                                    <th>Moneda</th>
                                    <th class="text-right">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movimientos as $mov)
                                    <tr>
                                        <td>{{ $mov->created_at->format('H:i') }}</td>
                                        <td>
                                            @php
                                                $tipoEtiqueta = match($mov->tipo) {
                                                    'cobro' => 'Cobro en efectivo',
                                                    'vuelto' => 'Vuelto entregado',
                                                    'ingreso' => 'Ingreso manual',
                                                    'egreso' => 'Egreso manual',
                                                    default => $mov->tipo,
                                                };
                                                $colorBadge = match($mov->tipo) {
                                                    'cobro', 'ingreso' => 'kitchen-status',
                                                    'vuelto', 'egreso' => 'kitchen-status-danger',
                                                    default => '',
                                                };
                                            @endphp
                                            <span class="{{ $colorBadge }}">{{ $tipoEtiqueta }}</span>
                                        </td>
                                        <td>{{ $mov->concepto }}</td>
                                        <td>{{ $mov->moneda?->codigo ?? '—' }}</td>
                                        <td class="text-right font-mono font-medium">
                                            {{ in_array($mov->tipo, ['vuelto', 'egreso']) ? '-' : '+' }} {{ number_format((float) $mov->importe, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
