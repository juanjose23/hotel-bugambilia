<x-filament-panels::page>
    <div wire:poll.6s="cargarPedidos" class="space-y-6 font-sans select-none pb-12">

        {{-- Encabezado principal --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-gray-900 p-4 sm:p-6 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-xs">
            <div class="flex items-center gap-3.5">
                <div class="p-3 rounded-xl bg-primary-500/10 text-primary-600 dark:text-primary-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-fire" class="w-7 h-7 animate-pulse" />
                </div>
                <div>
                    <h1 class="text-lg sm:text-xl font-black text-gray-950 dark:text-white tracking-tight">
                        Cocina
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Alertas sonoras y sincronización automática en tiempo real para comandas
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0 self-start sm:self-auto">
                {{-- Botón para desbloquear el audio en navegadores táctiles/escritorio --}}
                <button type="button" id="btn-activar-audio" onclick="desbloquearAudio()" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                    <x-filament::icon icon="heroicon-o-speaker-wave" class="w-4 h-4" />
                    <span>Activar Alertas de Audio</span>
                </button>

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



        {{-- Grid de Comandas KDS --}}
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

    {{-- Script de Audio Robusto y Desbloqueo por Interacción --}}
    <script>
        let globalAudioCtx = null;

        function desbloquearAudio() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                globalAudioCtx = new AudioContext();

                // Forzar resume si está suspendido
                if (globalAudioCtx.state === 'suspended') {
                    globalAudioCtx.resume();
                }

                // Reproducir un tono corto de prueba para confirmar activación
                const osc = globalAudioCtx.createOscillator();
                const gain = globalAudioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, globalAudioCtx.currentTime); // D5
                gain.gain.setValueAtTime(0.2, globalAudioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, globalAudioCtx.currentTime + 0.2);
                osc.connect(gain);
                gain.connect(globalAudioCtx.destination);
                osc.start();
                osc.stop(globalAudioCtx.currentTime + 0.2);

                // Ocultar o cambiar el botón para mostrar que ya está activo
                const btn = document.getElementById('btn-activar-audio');
                if (btn) {
                    btn.innerHTML = '<span>Audio Activado ✓</span>';
                    btn.classList.remove('bg-primary-600', 'hover:bg-primary-500');
                    btn.classList.add('bg-success-600', 'cursor-default');
                }
            } catch (e) {
                console.error('Error al inicializar el contexto de audio:', e);
            }
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('nuevo-pedido-audio', () => {
                try {
                    if (!globalAudioCtx) {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        globalAudioCtx = new AudioContext();
                    }

                    if (globalAudioCtx.state === 'suspended') {
                        globalAudioCtx.resume();
                    }

                    const playTone = (frequency, startTime, duration) => {
                        const osc = globalAudioCtx.createOscillator();
                        const gain = globalAudioCtx.createGain();

                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(frequency, globalAudioCtx.currentTime + startTime);

                        gain.gain.setValueAtTime(0, globalAudioCtx.currentTime + startTime);
                        gain.gain.linearRampToValueAtTime(0.6, globalAudioCtx.currentTime + startTime + 0.03);
                        gain.gain.exponentialRampToValueAtTime(0.001, globalAudioCtx.currentTime + startTime + duration);

                        osc.connect(gain);
                        gain.connect(globalAudioCtx.destination);

                        osc.start(globalAudioCtx.currentTime + startTime);
                        osc.stop(globalAudioCtx.currentTime + startTime + duration);
                    };

                    // Doble timbre de cocina
                    playTone(880.00, 0, 0.4);
                    playTone(1318.51, 0.15, 0.6);

                } catch (e) {
                    console.log('Audio prevenido: Haga clic en el botón "Activar Alertas de Audio"', e);
                }
            });
        });
    </script>
</x-filament-panels::page>
