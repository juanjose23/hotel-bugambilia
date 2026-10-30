<div class="kitchen-shell">
    <div class="kitchen-callout"><strong>{{ $guia['nombre'] }}</strong><br>Referencia de existencias: {{ $guia['bodega'] }}. Pesa o cuenta cada ingrediente en la unidad indicada. Todavía no se ha retirado inventario.</div>
    <div class="kitchen-panel"><div class="kitchen-table-wrap"><table class="kitchen-table"><thead><tr><th>Ingrediente</th><th>Necesitas</th><th>Disponible</th><th>Qué hacer</th></tr></thead><tbody>
        @forelse($guia['ingredientes'] as $ingrediente)<tr><td data-label="Ingrediente">{{ $ingrediente['nombre'] }}</td><td data-label="Necesitas">{{ number_format($ingrediente['necesaria'], 3, ',', '.') }} {{ $ingrediente['unidad'] }}</td><td data-label="Disponible">{{ number_format($ingrediente['disponible'], 3, ',', '.') }} {{ $ingrediente['unidad'] }}</td><td data-label="Qué hacer">@if($ingrediente['faltante'] > 0)<span class="kitchen-status kitchen-status-danger">Faltan {{ number_format($ingrediente['faltante'], 3, ',', '.') }} {{ $ingrediente['unidad'] }} · solicita reposición antes de preparar</span>@else<span class="kitchen-status">Cantidad suficiente</span>@endif</td></tr>
        @empty<tr><td colspan="4">La receta no tiene ingredientes configurados. Pide al encargado que complete la ficha antes de producir.</td></tr>@endforelse
    </tbody></table></div></div>
    <p class="kitchen-review-note">La disponibilidad se comprueba de nuevo al confirmar. En ese momento puedes revisar la bodega real de preparación. Los costos se calculan con los lotes consumidos.</p>
</div>
