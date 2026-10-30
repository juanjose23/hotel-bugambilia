<x-filament-panels::page><div class="kitchen-shell">
 @if(config('cocina.demo'))<div class="kitchen-demo">Demostración · operaciones ficticias</div>@endif
 <div class="kitchen-intro"><div><h2>Conciliación bancaria</h2><p>Conserva la evidencia del extracto y relaciónala con el libro. Los cobros capturados quedan pendientes hasta verificar su liquidación.</p></div></div>
 {{ $this->table }}
 </div></x-filament-panels::page>
