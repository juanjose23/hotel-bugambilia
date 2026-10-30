<x-filament-panels::page><div class="kitchen-shell">
 @if(config('cocina.demo'))<div class="kitchen-demo">Demostración · operaciones ficticias</div>@endif
 <div class="kitchen-intro"><div><h2>Comprobantes pendientes</h2><p>Subir una imagen no acredita dinero. Revisa la evidencia y contrástala con la cuenta, fecha, referencia e importe del banco.</p></div></div>
 {{ $this->table }}
 </div></x-filament-panels::page>
