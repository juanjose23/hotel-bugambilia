<x-filament-panels::page>
    <div wire:poll.6s="cargarPedidos" class="space-y-6 font-sans select-none">
        
        {{-- Encabezado principal --}}
        <x-restaurante.page-header
            icon="heroicon-o-fire"
            title="Cocina KDS — Panel de Preparación"
            subtitle="Notificaciones sonoras en tiempo real al ingresar nuevas comandas"
        >
            <x-slot name="actions">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-mono font-bold text-gray-700 dark:text-gray-300">
                    <x-filament::icon icon="heroicon-o-arrow-path" class="w-3.5 h-3.5" />
                    Polling 6s
                </span>
            </x-slot>
        </x-restaurante.page-header>

        {{-- Componente de Barra de Filtro de Área KDS --}}
        <x-restaurante.kds-area-bar :areaSeleccionada="$areaSeleccionada" />

        {{-- Grid de Comandas KDS --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($this->pedidos as $pedido)
                <x-restaurante.pedido-card 
                    modo="kds" 
                    :pedido="$pedido" 
                    :tiempoTranscurrido="$this->tiempoTranscurrido($pedido)" 
                />
            @empty
                <div class="col-span-full text-center py-16 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 max-w-md mx-auto p-6 shadow-xs">
                    <div class="p-3 bg-[rgba(107,0,62,0.1)] rounded-full w-fit mx-auto mb-3 text-[#6b003e] dark:text-[#e87faa]">
                        <x-filament::icon icon="heroicon-o-check-badge" class="w-8 h-8" />
                    </div>
                    <h3 class="text-base font-bold text-gray-950 dark:text-white mb-1">Sin pedidos pendientes</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">La estación KDS está al día. Todos los platillos seleccionados se encuentran preparados.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Audio Alert Synthesizer Script para Cocina KDS --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('nuevo-pedido-audio', () => {
                try {
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(783.99, audioCtx.currentTime); // G5
                    osc.frequency.setValueAtTime(1046.50, audioCtx.currentTime + 0.18); // C6
                    
                    gain.gain.setValueAtTime(0.4, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.6);
                    
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.6);
                } catch (e) {
                    console.log('Audio de KDS prevenido por políticas del navegador', e);
                }
            });
        });
    </script>
</x-filament-panels::page>
