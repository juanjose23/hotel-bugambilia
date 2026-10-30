<x-filament-panels::page>
    <div class="kitchen-shell">
        @if(config('cocina.demo'))<div class="kitchen-demo">Demostración · fondos y operaciones ficticios</div>@endif
        <section class="kitchen-hero"><div><p class="kitchen-eyebrow">CONTROL DEL EFECTIVO</p><h2>Cada caja, un responsable.</h2><p>Crea las cajas del hotel, define sus monedas y revisa quién tiene el fondo a cargo.</p></div></section>
        <div class="kitchen-steps"><div><strong>1 · Crea la caja</strong><p>Identifica dónde se recibe efectivo y las monedas que manejará.</p></div><div><strong>2 · Abre un turno</strong><p>El cajero cuenta el fondo e identifica quién lo entrega.</p></div><div><strong>3 · Revisa el arqueo</strong><p>Los faltantes y sobrantes se conservan y requieren aprobación de otro usuario autorizado.</p></div></div>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
