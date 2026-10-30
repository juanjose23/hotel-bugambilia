<x-filament-panels::page>
    <div class="kitchen-shell">
        @if(config('cocina.demo'))<div class="kitchen-demo">Demostración · fondos y operaciones ficticios</div>@endif
        <div class="kitchen-intro"><div><h2>Recibe, registra y cuenta.</h2><p>El saldo esperado conserva las monedas separadas. Cada cobro registra el efectivo recibido y el vuelto entregado.</p></div></div>
        @if($sesion)@include('filament.pages.tesoreria.resumen-cierre')@else<div class="kitchen-callout">Abre tu turno, cuenta el fondo recibido y después registra los cobros.</div>@endif
        {{ $this->table }}
    </div>
</x-filament-panels::page>
