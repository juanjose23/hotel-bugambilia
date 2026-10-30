<x-filament-panels::page><div class="kitchen-shell">
 @if(config('cocina.demo'))<div class="kitchen-demo">Demostración · operaciones ficticias</div>@endif
 <div class="kitchen-intro"><div><h2>Entregas y depósitos</h2><p>El efectivo sale del turno y queda en tránsito hasta que otro responsable confirma su recepción o el banco acredita el depósito.</p></div></div>
 {{ $this->table }}
 </div></x-filament-panels::page>
