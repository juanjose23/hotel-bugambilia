<x-filament-panels::page>
    @if ($this->activeTab === 'solicitudes')
        @include('filament.resources.compras.cotizaciones.tabs.solicitudes-resumen')
    @else
        {{ $this->table }}
    @endif
</x-filament-panels::page>
