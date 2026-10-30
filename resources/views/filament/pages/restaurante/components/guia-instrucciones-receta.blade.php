<div class="kitchen-shell">
    <div class="kitchen-steps"><div><strong>1 · Reúne y mide</strong><p>Revisa la lista de ingredientes. Si falta algo, solicita reposición; evita cambiar la receta sin autorización.</p></div><div><strong>2 · Sigue la ficha del platillo</strong><p>Trabaja con las instrucciones aprobadas por el encargado. La pantalla muestra abajo los pasos registrados para esta receta.</p></div><div><strong>3 · Cuenta el resultado real</strong><p>Al terminar, pesa o cuenta lo obtenido. Guarda esta planificación y abre «Confirmar preparación» para ingresar el resultado a cocina.</p></div></div>
    <div class="kitchen-review"><h3>Ficha de preparación · {{ $guia['nombre'] }}</h3>
        @forelse($guia['pasos'] as $paso)<div class="kitchen-review-row"><strong>{{ $loop->iteration }}.</strong><p>{{ $paso }}</p></div>
        @empty<p class="kitchen-review-note">Este platillo aún no tiene instrucciones de preparación. El encargado debe registrarlas en el catálogo de platillos; esta guía no sustituye la ficha de cocción.</p>@endforelse
    </div>
    <div class="kitchen-callout"><strong>Guardar planificación no descuenta ingredientes.</strong><br>Después de guardar, usa «Confirmar preparación»: elige la bodega, revisa ingredientes y registra cantidad obtenida y vencimiento. Así se evita descontar dos veces.</div>
</div>
