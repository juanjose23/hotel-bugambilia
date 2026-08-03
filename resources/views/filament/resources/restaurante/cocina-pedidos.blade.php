<x-filament-panels::page>
    <div wire:poll.6s="cargarPedidos" class="space-y-6 font-sans select-none pb-12">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-gray-900 p-3 sm:p-6 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="p-2 sm:p-3 rounded-xl bg-red-500/10 text-red-500 dark:text-red-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-fire" class="w-5 h-5 sm:w-7 sm:h-7 animate-pulse" />
                </div>
                <div>
                    <h1 class="text-sm sm:text-xl font-black text-gray-950 dark:text-white tracking-tight">
                        Cocina KDS
                    </h1>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 mt-0.5 hidden sm:block">
                        Alertas sonoras y sincronización en tiempo real
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0 self-start sm:self-auto">
                <button type="button" id="btn-activar-audio-kds" onclick="desbloquearAudioKDS()"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-[10px] sm:text-xs font-bold transition shadow-xs cursor-pointer">
                    <x-filament::icon icon="heroicon-o-speaker-wave" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    <span class="hidden sm:inline">Activar Audio</span>
                </button>

                <span class="inline-flex items-center gap-1.5 px-2 py-1 sm:px-3 sm:py-1.5 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 text-[10px] sm:text-xs font-mono font-bold text-gray-700 dark:text-gray-300">
                    <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2 bg-success-500"></span>
                    </span>
                    6s
                </span>
            </div>
        </div>

        {{-- Grid KDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-3 sm:gap-6 items-start">
            @forelse($this->pedidos as $pedido)
                <div class="transition-all duration-200 hover:-translate-y-0.5">
                    <x-restaurante.pedido-card
                        modo="kds"
                        :pedido="$pedido"
                        :tiempoTranscurrido="$this->tiempoTranscurrido($pedido)"
                    />
                </div>
            @empty
                <div class="col-span-full text-center py-12 sm:py-20 bg-white dark:bg-gray-900 rounded-3xl border border-dashed border-gray-300 dark:border-gray-800 max-w-lg mx-auto p-6 sm:p-8 shadow-xs">
                    <div class="p-3 sm:p-4 bg-primary-500/10 rounded-2xl w-fit mx-auto mb-3 sm:mb-4 text-primary-600 dark:text-primary-400">
                        <x-filament::icon icon="heroicon-o-check-badge" class="w-8 h-8 sm:w-10 sm:h-10" />
                    </div>
                    <h3 class="text-sm sm:text-lg font-bold text-gray-950 dark:text-white mb-1">Sin comandas pendientes</h3>
                    <p class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        Las nuevas comandas aparecerán automáticamente al ser emitidas.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        let audioCtxKDS = null;

        function desbloquearAudioKDS() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                audioCtxKDS = new AudioContext();
                if (audioCtxKDS.state === 'suspended') audioCtxKDS.resume();

                const osc = audioCtxKDS.createOscillator();
                const gain = audioCtxKDS.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, audioCtxKDS.currentTime);
                gain.gain.setValueAtTime(0.15, audioCtxKDS.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtxKDS.currentTime + 0.15);
                osc.connect(gain);
                gain.connect(audioCtxKDS.destination);
                osc.start();
                osc.stop(audioCtxKDS.currentTime + 0.15);

                const btn = document.getElementById('btn-activar-audio-kds');
                if (btn) {
                    btn.innerHTML = '<span class="text-[10px] sm:text-xs">Audio On</span>';
                    btn.classList.remove('bg-primary-600', 'hover:bg-primary-500');
                    btn.classList.add('bg-emerald-600', 'cursor-default');
                }
            } catch (e) {
                console.error('Audio error:', e);
            }
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('nuevo-pedido-audio', () => {
                if (!audioCtxKDS) return;
                try {
                    if (audioCtxKDS.state === 'suspended') audioCtxKDS.resume();
                    const t = audioCtxKDS.currentTime;
                    const play = (f, s, d) => {
                        const o = audioCtxKDS.createOscillator();
                        const g = audioCtxKDS.createGain();
                        o.type = 'sine';
                        o.frequency.setValueAtTime(f, t + s);
                        g.gain.setValueAtTime(0, t + s);
                        g.gain.linearRampToValueAtTime(0.5, t + s + 0.03);
                        g.gain.exponentialRampToValueAtTime(0.001, t + s + d);
                        o.connect(g); g.connect(audioCtxKDS.destination);
                        o.start(t + s); o.stop(t + s + d);
                    };
                    play(880, 0, 0.4);
                    play(1318.51, 0.15, 0.6);
                } catch (e) {}
            });
        });
    </script>
</x-filament-panels::page>
