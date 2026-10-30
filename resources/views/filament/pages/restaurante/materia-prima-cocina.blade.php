<x-filament-panels::page>
    <div class="kitchen-shell">
        <div class="kitchen-page-intro"><div><p class="kitchen-eyebrow">INSUMOS Y PREPARACIONES</p><h2>De materia prima a producto listo.</h2><p>Elige el insumo, registra lo obtenido y revisa todo antes de guardar.</p></div><x-filament::button tag="a" :href="\App\Filament\Pages\Restaurante\ControlCocina::getUrl()" color="gray" icon="heroicon-o-arrow-left">Volver al control</x-filament::button></div>
        @if(session('cocina_preparacion_guardada'))<div class="kitchen-callout kitchen-callout-success">Preparación {{ session('cocina_preparacion_guardada') }} registrada. Puedes revisar el resultado en Movimientos recientes.</div>@endif
        <form wire:submit="guardar" data-guia="preparacion-formulario" class="kitchen-form">{{ $this->form }}</form>
    </div>
</x-filament-panels::page>
