<div class="kitchen-shell">
    <div class="kitchen-callout"><strong>{{ match ($menu['servicio']) { 'autoservicio' => 'Las personas se sirven', 'mesero' => 'El mesero sirve las porciones', 'mixto' => 'Servicio mixto', default => 'Falta indicar quién sirve' } }}</strong><br>
        @if($menu['servicio'] === 'autoservicio')Las personas eligen los platillos. Registra bandejas enviadas y reposiciones; al terminar pesa sobrantes y desperdicios. La cantidad servida se calcula en el cierre.
        @elseif($menu['servicio'] === 'mesero')El mesero entrega las porciones de los platillos del menú. Mantén la porción indicada en la ficha, registra reposiciones y mide sobrantes al cerrar.
        @elseif($menu['servicio'] === 'mixto')Indica al equipo qué platillos son de autoservicio y cuáles entrega el mesero. Registra todos los envíos y concilia al finalizar.
        @elseOrganiza el menú y elige quién sirve antes de iniciar el servicio.@endif
    </div>
    <div class="kitchen-panel"><div class="kitchen-table-wrap"><table class="kitchen-table"><thead><tr><th>Platillo</th><th>Presentación lista para servir</th><th>Porciones previstas</th></tr></thead><tbody>
        @forelse($menu['platos'] as $plato)<tr><td data-label="Platillo">{{ $plato['nombre'] }}</td><td data-label="Presentación">{{ $plato['presentacion'] }}</td><td data-label="Porciones previstas">{{ $plato['porciones'] }}</td></tr>
        @empty<tr><td colspan="3">Este servicio todavía no tiene menú. Usa «Organizar menú» para relacionar sus platillos.</td></tr>@endforelse
    </tbody></table></div></div>
    <p class="kitchen-review-note">Preparar descuenta los ingredientes. Enviar al buffet traslada el producto terminado. Servir no vuelve a descontar la receta ni genera otro cobro por cada platillo. El cobro sigue la modalidad del evento. Una persona puede elegir varios platillos: las porciones previstas no se suman como personas por cobrar.</p>
</div>
