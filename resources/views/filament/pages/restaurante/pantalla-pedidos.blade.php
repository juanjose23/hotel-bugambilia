<x-filament-panels::page>
    <div wire:poll.4s="cargarPedidos" class="space-y-6 font-sans select-none">

        {{-- Encabezado con componente compartido --}}
        <x-restaurante.page-header
            icon="heroicon-o-tv"
            title="Pantalla de Turnos — Comedor & Despacho"
            subtitle="Estado de comandas en vivo • Actualización automática"
        >
            <x-slot name="actions">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-emerald-500/40 text-xs font-mono font-bold text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    SEÑAL EN VIVO
                </span>

                <x-filament::button
                    tag="a"
                    href="{{ route('pantalla-turnos') }}"
                    target="_blank"
                    color="gray"
                    icon="heroicon-o-arrow-top-right-on-square"
                    size="sm"
                >
                    Vista Cliente (Pública)
                </x-filament::button>

                <x-filament::button
                    type="button"
                    onclick="document.documentElement.requestFullscreen()"
                    color="gray"
                    icon="heroicon-o-arrows-pointing-out"
                    size="sm"
                >
                    Pantalla Completa
                </x-filament::button>
            </x-slot>
        </x-restaurante.page-header>

        {{-- Paneles de Turno de 2 Columnas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Columna 1: EN PREPARACIÓN --}}
            <x-restaurante.turno-panel
                titulo="En Preparación"
                subtitulo="Cocina procesando comanda"
                icon="heroicon-o-clock"
                badgeText="{{ $this->pedidosEnPreparacion->count() }} Órdenes"
                badgeColor="warning"
                :pedidos="$this->pedidosEnPreparacion"
                modo="turno"
                emptyIcon="heroicon-o-check-circle"
                emptyText="Sin comandas pendientes en cocina."
                :componenteRef="$this"
            />

            {{-- Columna 2: LISTO PARA RETIRAR --}}
            <x-restaurante.turno-panel
                titulo="Listo para Retirar"
                subtitulo="Comanda preparada por cocina"
                icon="heroicon-o-check-badge"
                badgeText="{{ $this->pedidosListos->count() }} Listos"
                badgeColor="success"
                :pedidos="$this->pedidosListos"
                modo="publico"
                emptyIcon="heroicon-o-sparkles"
                emptyText="Esperando platillos preparados de la cocina..."
                :componenteRef="$this"
            />

        </div>

    </div>

    {{-- Audio Alert Synthesizer Script para Pantalla Turnos --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('pedido-listo-audio', () => {
                try {
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.15); // A5

                    gain.gain.setValueAtTime(0.35, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);

                    osc.connect(gain);
                    gain.connect(audioCtx.destination);

                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.5);
                } catch (e) {
                    console.log('Audio de turno prevenido por interacción del navegador', e);
                }
            });
        });
    </script>
</x-filament-panels::page>
