<x-filament-panels::page><div class="kitchen-shell">
 @if(config('cocina.demo'))<div class="kitchen-demo">Demostración · operaciones ficticias</div>@endif
 <div class="kitchen-intro"><div><h2>Devoluciones de efectivo</h2><p>El pago original se conserva. Otro usuario autoriza la devolución y la salida queda vinculada al pago y al turno.</p></div></div>
 {{ $this->table }}
 </div></x-filament-panels::page>
