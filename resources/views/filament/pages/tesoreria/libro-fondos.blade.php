<x-filament-panels::page><div class="kitchen-shell">
 @if(config('cocina.demo'))<div class="kitchen-demo">Demostración · operaciones ficticias</div>@endif
 <div class="kitchen-intro"><div><h2>Libro de fondos</h2><p>El libro conserva entradas y salidas. Las transferencias aparecen en ambos fondos y los movimientos confirmados no se borran.</p></div></div>
 {{ $this->table }}
 </div></x-filament-panels::page>
