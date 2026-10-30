<x-filament-panels::page><div class="kitchen-shell">
 @if(config('cocina.demo'))<div class="kitchen-demo">Demostración · operaciones ficticias</div>@endif
 <div class="kitchen-intro"><div><h2>Caja chica</h2><p>Solicita un vale, obtiene autorización y liquida el gasto con su comprobante y el remanente devuelto.</p></div></div>
 {{ $this->table }}
 </div></x-filament-panels::page>
