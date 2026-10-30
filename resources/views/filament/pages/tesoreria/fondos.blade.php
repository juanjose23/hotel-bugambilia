<x-filament-panels::page><div class="kitchen-shell">
 @if(config('cocina.demo'))<div class="kitchen-demo">Demostración · operaciones ficticias</div>@endif
 <div class="kitchen-intro"><div><h2>Fondos y bancos</h2><p>Consulta saldos por moneda, documenta los fondos y repón caja chica desde un origen identificado.</p></div></div>
 {{ $this->table }}
 </div></x-filament-panels::page>
