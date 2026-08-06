<x-filament-panels::page>
    <div wire:poll.4s="cargarPedidos" class="space-y-6 font-sans select-none pb-12">

        {{-- Encabezado de Turnos / TV --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white/95 dark:bg-gray-900/95 p-4 sm:p-6 rounded-3xl border border-gray-200/80 dark:border-gray-800 shadow-lg backdrop-blur-md">
            <div class="flex items-center gap-3.5 sm:gap-4">
                <div class="p-3.5 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 text-emerald-600 dark:text-emerald-400 shrink-0 border border-emerald-500/30 shadow-inner">
                    <x-filament::icon icon="heroicon-o-tv" class="w-6 h-6 sm:w-8 sm:h-8" />
                </div>
                <div>
                    <h1 class="text-lg sm:text-2xl font-black text-gray-950 dark:text-white tracking-tight font-mono">
                        Pantalla de Turnos & Despacho
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 hidden sm:block">
                        Monitoreo y despacho en tiempo real de comedores y entregas
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0 self-start sm:self-auto">
                <button type="button" id="btn-activar-audio-turnos" onclick="desbloquearAudioTurnos()" wire:ignore
                    data-audio-label="turnos"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 rounded-2xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-semibold transition shadow-xs cursor-pointer">
                    <x-filament::icon icon="heroicon-o-speaker-wave" class="w-4 h-4" />
                    <span>Activar Voz</span>
                </button>

                <span class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 rounded-2xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-mono font-extrabold text-gray-800 dark:text-gray-200 shadow-inner">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    EN VIVO (4s)
                </span>
            </div>
        </div>

        {{-- Grid de Pedidos Activos --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 sm:gap-6 items-start">
            @forelse($this->pedidos as $pedido)
                <div class="transition-all duration-300 hover:-translate-y-1">
                    <x-restaurante.pedido-card
                        modo="turno"
                        :pedido="$pedido"
                        :tiempoTranscurrido="$this->tiempoTranscurrido($pedido)"
                    />
                </div>
            @empty
                <div class="col-span-full text-center py-16 sm:py-24 bg-white/95 dark:bg-gray-900/95 rounded-3xl border border-dashed border-gray-300 dark:border-gray-800 max-w-lg mx-auto p-8 shadow-xl backdrop-blur-md">
                    <div class="p-4 bg-emerald-500/10 rounded-3xl w-fit mx-auto mb-4 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-inner">
                        <x-filament::icon icon="heroicon-o-check-badge" class="w-10 h-10 sm:w-12 sm:h-12" />
                    </div>
                    <h3 class="text-base sm:text-xl font-black text-gray-950 dark:text-white mb-2 tracking-tight">Sin comandas activas</h3>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        Todas las órdenes han sido despachadas. Las nuevas comandas aparecerán automáticamente.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- Modal: Cobro / Pago (Acciones Filament)    --}}
    {{-- ═══════════════════════════════════════════ --}}
    <x-filament-actions::modals />

    <script>
        window.audioCtxTurnos = window.audioCtxTurnos || null;
        window.audioTurnosEnabled = window.audioTurnosEnabled || localStorage.getItem('kdsAudioEnabled') === '1';

        function obtenerAudioTurnos() {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return null;

            if (!window.audioCtxTurnos) {
                window.audioCtxTurnos = new AudioContext();
            }

            return window.audioCtxTurnos;
        }

        async function desbloquearAudioTurnos() {
            try {
                const audioCtxTurnos = obtenerAudioTurnos();
                if (!audioCtxTurnos) return;

                if (audioCtxTurnos.state === 'suspended') {
                    await audioCtxTurnos.resume();
                }

                window.audioTurnosEnabled = true;
                localStorage.setItem('kdsAudioEnabled', '1');
                reproducirPulsoTurnos(659.25, 0, 0.15, 0.14);
                hablarTurnos('Audio activado');
                pintarEstadoAudioTurnos();
            } catch (e) {
                console.error('Audio turnos error:', e);
            }
        }

        function pintarEstadoAudioTurnos() {
            window.audioTurnosEnabled = localStorage.getItem('kdsAudioEnabled') === '1';
            const btn = document.getElementById('btn-activar-audio-turnos');
            if (!btn || !window.audioTurnosEnabled) return;

            btn.innerHTML = '<span class="text-[10px] sm:text-xs">Voz On</span>';
            btn.classList.remove('bg-primary-600', 'hover:bg-primary-500');
            btn.classList.add('bg-emerald-600', 'cursor-default');
        }

        function reproducirPulsoTurnos(frecuencia, inicio, duracion, volumen = 0.4) {
            const audioCtxTurnos = obtenerAudioTurnos();
            if (!audioCtxTurnos || audioCtxTurnos.state !== 'running') return;

            const t = audioCtxTurnos.currentTime;
            const osc = audioCtxTurnos.createOscillator();
            const gain = audioCtxTurnos.createGain();

            try {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(frecuencia, t + inicio);
                gain.gain.setValueAtTime(0, t + inicio);
                gain.gain.linearRampToValueAtTime(volumen, t + inicio + 0.03);
                gain.gain.exponentialRampToValueAtTime(0.001, t + inicio + duracion);
                osc.connect(gain);
                gain.connect(audioCtxTurnos.destination);
                osc.start(t + inicio);
                osc.stop(t + inicio + duracion);
            } catch (e) {
                console.error('Audio turnos error:', e);
            }
        }

        function hablarTurnos(texto) {
            if (!('speechSynthesis' in window)) return;

            try {
                window.speechSynthesis.cancel();
                const mensaje = new SpeechSynthesisUtterance(texto);
                mensaje.lang = 'es-NI';
                mensaje.rate = 0.95;
                mensaje.pitch = 1;
                mensaje.volume = 0.85;
                window.speechSynthesis.speak(mensaje);
            } catch (e) {
                console.error('Speech turnos error:', e);
            }
        }

        function resolverMesaEventoTurnos(evento) {
            if (evento?.mesa) {
                return evento.mesa;
            }

            const detalle = evento?.detail;

            if (detalle?.mesa) {
                return detalle.mesa;
            }

            if (Array.isArray(detalle) && detalle[0]?.mesa) {
                return detalle[0].mesa;
            }

            return 'sin mesa';
        }

        function reproducirPedidoListoTurnos(evento = null) {
            window.audioTurnosEnabled = localStorage.getItem('kdsAudioEnabled') === '1';
            if (!window.audioTurnosEnabled) return;
            const audioCtxTurnos = obtenerAudioTurnos();
            const mesa = resolverMesaEventoTurnos(evento);

            if (!audioCtxTurnos || audioCtxTurnos.state !== 'running') {
                pintarEstadoAudioTurnos();
            } else {
                reproducirPulsoTurnos(783.99, 0, 0.25);
                reproducirPulsoTurnos(1046.5, 0.12, 0.35);
            }

            hablarTurnos(`Pedido listo para ser servido, ${mesa}`);
        }

        window.addEventListener('pedido-listo-audio', reproducirPedidoListoTurnos);
        document.addEventListener('pedido-listo-audio', reproducirPedidoListoTurnos);
        document.addEventListener('DOMContentLoaded', pintarEstadoAudioTurnos);
        setInterval(pintarEstadoAudioTurnos, 1000);
        document.addEventListener('click', () => {
            if (window.audioTurnosEnabled) {
                obtenerAudioTurnos()?.resume?.();
                pintarEstadoAudioTurnos();
            }
        }, { once: false });

        document.addEventListener('livewire:init', () => {
            pintarEstadoAudioTurnos();

            if (window.Livewire) {
                window.Livewire.on('pedido-listo-audio', reproducirPedidoListoTurnos);
                window.Livewire.hook?.('morphed', pintarEstadoAudioTurnos);
            }
        });

        document.addEventListener('livewire:initialized', () => {
            pintarEstadoAudioTurnos();

            if (window.Livewire) {
                window.Livewire.on('pedido-listo-audio', reproducirPedidoListoTurnos);
                window.Livewire.hook?.('morphed', pintarEstadoAudioTurnos);
            }
        });
    </script>
</x-filament-panels::page>
