<x-filament-panels::page>
    <div wire:poll.6s="cargarPedidos" class="space-y-6 font-sans select-none pb-12">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-gray-900 p-4 sm:p-6 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-xs">
            <div class="flex items-center gap-3.5">
                <div class="p-3 rounded-xl bg-primary-500/10 text-primary-600 dark:text-primary-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-fire" class="w-7 h-7 animate-pulse" />
                </div>
                <div>
                    <h1 class="text-lg sm:text-xl font-black text-gray-950 dark:text-white tracking-tight">
                        Cocina KDS — Panel de Preparación
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Alertas sonoras y sincronización automática en tiempo real para comandas
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0 self-start sm:self-auto">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 text-xs font-mono font-bold text-gray-700 dark:text-gray-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-success-500"></span>
                    </span>
                    <x-filament::icon icon="heroicon-o-arrow-path" class="w-3.5 h-3.5 animate-spin" style="animation-duration: 4s;" />
                    Polling 6s
                </span>
            </div>
        </div>



        {{-- Grid de Comandas KDS Dinámico (1 col en móvil, 2 en tablets, hasta 4 en pantallas ultra anchas de cocina) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-5 sm:gap-6 items-start">
            @forelse($this->pedidos as $pedido)
                <div class="transition-all duration-200 hover:-translate-y-0.5">
                    <x-restaurante.pedido-card
                        modo="kds"
                        :pedido="$pedido"
                        :tiempoTranscurrido="$this->tiempoTranscurrido($pedido)"
                    />
                </div>
            @empty
                <div class="col-span-full text-center py-20 bg-white dark:bg-gray-900 rounded-3xl border border-dashed border-gray-300 dark:border-gray-800 max-w-lg mx-auto p-8 shadow-xs">
                    <div class="p-4 bg-primary-500/10 rounded-2xl w-fit mx-auto mb-4 text-primary-600 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-check-badge" class="w-10 h-10" />
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-950 dark:text-white mb-1">¡Todo limpio por aquí!</h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        No hay pedidos pendientes en esta estación KDS. Las nuevas comandas aparecerán automáticamente en cuanto sean emitidas.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Script de Alerta de Audio Sintetizado Mejorado (Doble Timbre de Cocina Profesional) --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('nuevo-pedido-audio', () => {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    const audioCtx = new AudioContext();
                    const playTone = (frequency, startTime, duration) => {
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();

                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(frequency, audioCtx.currentTime + startTime);

                        gain.gain.setValueAtTime(0, audioCtx.currentTime + startTime);
                        gain.gain.linearRampToValueAtTime(0.5, audioCtx.currentTime + startTime + 0.03);
                        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + startTime + duration);

                        osc.connect(gain);
                        gain.connect(audioCtx.destination);

                        osc.start(audioCtx.currentTime + startTime);
                        osc.stop(audioCtx.currentTime + startTime + duration);
                    };

                    playTone(880.00, 0, 0.4);
                    playTone(1318.51, 0.15, 0.6);

                } catch (e) {
                    console.log('Reproducción de audio bloqueada por políticas de interacción del navegador', e);
                }
            });
        });
    </script>
</x-filament-panels::page>
