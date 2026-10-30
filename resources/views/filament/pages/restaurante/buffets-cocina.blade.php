<x-filament-panels::page>
    <div class="kitchen-shell">
        <div class="kitchen-intro"><div><h2>Del primer envío al cierre del servicio</h2><p>Controla buffets por persona, paquetes de evento y personas adicionales. Cada reposición conserva su cantidad y unidad.</p></div><a href="{{ \App\Filament\Pages\Restaurante\ControlCocina::getUrl() }}" wire:navigate>← Control de cocina</a></div>
        <div data-guia="buffet-flujo" class="kitchen-steps"><div><strong>01 · Organiza el servicio</strong><p>Elige los platillos y quién sirve: la persona, un mesero o ambos. Después define asistentes y precios.</p></div><div><strong>02 · Envía y repón</strong><p>Prepara cada receta y envía el platillo terminado. Registra las reposiciones sin volver a descontar ingredientes.</p></div><div><strong>03 · Mide y cierra</strong><p>Cuenta asistentes, pesa sobrantes y desperdicios. Después registra el cargo en su cuenta.</p></div></div>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
