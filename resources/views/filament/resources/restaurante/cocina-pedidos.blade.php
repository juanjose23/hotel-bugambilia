<x-filament-panels::page>
    <div wire:poll.6s="cargarPedidos" class="space-y-6 font-sans select-none pb-12">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl p-4 sm:p-5 rounded-3xl border border-gray-200/80 dark:border-gray-800 shadow-sm">
            <div class="flex items-center gap-3.5">
                <div class="p-3 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-fire" class="w-6 h-6 sm:w-7 sm:h-7 animate-pulse" />
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-base sm:text-lg font-black text-gray-950 dark:text-white tracking-tight">
                            Comandas en Preparación
                        </h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60">
                            {{ $this->pedidos->count() }} {{ $this->pedidos->count() === 1 ? 'comanda' : 'comandas' }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 hidden sm:block">
                        Monitoreo de tiempos, alertas sonoras y gestión de producción en tiempo real
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                {{-- Filtro de Áreas de Cocina --}}
                <div class="inline-flex items-center p-1 rounded-2xl bg-gray-100 dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/80 text-xs font-medium">
                    <button type="button" wire:click="$set('areaSeleccionada', null)"
                        class="px-3 py-1.5 rounded-xl transition font-semibold {{ $areaSeleccionada === null ? 'bg-white dark:bg-gray-900 text-gray-950 dark:text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-950 dark:hover:text-white' }}">
                        Todas
                    </button>
                    <button type="button" wire:click="$set('areaSeleccionada', 'cocina')"
                        class="px-3 py-1.5 rounded-xl transition font-semibold {{ $areaSeleccionada === 'cocina' ? 'bg-white dark:bg-gray-900 text-gray-950 dark:text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-950 dark:hover:text-white' }}">
                        Cocina
                    </button>
                    <button type="button" wire:click="$set('areaSeleccionada', 'postres')"
                        class="px-3 py-1.5 rounded-xl transition font-semibold {{ $areaSeleccionada === 'postres' ? 'bg-white dark:bg-gray-900 text-gray-950 dark:text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-950 dark:hover:text-white' }}">
                        Postres
                    </button>
                    <button type="button" wire:click="$set('areaSeleccionada', 'bar')"
                        class="px-3 py-1.5 rounded-xl transition font-semibold {{ $areaSeleccionada === 'bar' ? 'bg-white dark:bg-gray-900 text-gray-950 dark:text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-950 dark:hover:text-white' }}">
                        Barra
                    </button>
                    <button type="button" wire:click="$set('areaSeleccionada', 'parrilla')"
                        class="px-3 py-1.5 rounded-xl transition font-semibold {{ $areaSeleccionada === 'parrilla' ? 'bg-white dark:bg-gray-900 text-gray-950 dark:text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-950 dark:hover:text-white' }}">
                        Parrilla
                    </button>
                </div>

                {{-- Botón Pantalla Completa --}}
                <button type="button" onclick="toggleKDSFullscreen()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-semibold transition border border-gray-200 dark:border-gray-700 cursor-pointer"
                    title="Pantalla Completa KDS">
                    <x-filament::icon icon="heroicon-o-arrows-pointing-out" class="w-4 h-4" />
                    <span class="hidden sm:inline">Pantalla Completa</span>
                </button>

                {{-- Botón Audio KDS --}}
                <button type="button" id="btn-activar-audio-kds" onclick="desbloquearAudioKDS()" wire:ignore
                    data-audio-label="kds"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-semibold transition shadow-xs cursor-pointer">
                    <x-filament::icon icon="heroicon-o-speaker-wave" class="w-4 h-4" />
                    <span>Activar Voz</span>
                </button>

                {{-- Badge de Sincronización --}}
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700 text-xs font-mono font-bold text-gray-700 dark:text-gray-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
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

    <x-filament::modal id="modal-sustitucion-ingredientes" width="3xl">
        <x-slot name="heading">Sustituir ingredientes faltantes</x-slot>
        <x-slot name="description">
            Seleccione un sustituto por cada ingrediente para iniciar la preparación.
        </x-slot>

        <div class="space-y-4">
            {{ $this->sustitucionForm }}

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-xs text-gray-600 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-300">
                Para crear materia prima antes de sustituir, use <span class="font-semibold">Restaurante → Cocina → Materia Prima</span>.
                Ahí se transforma un producto origen, entra la materia prima al inventario y se registra la merma final.
            </div>
        </div>

        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'modal-sustitucion-ingredientes' })">
                Cancelar
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ \App\Filament\Pages\Restaurante\MateriaPrimaCocina::getUrl() }}"
                color="gray"
                icon="heroicon-o-beaker"
            >
                Crear materia prima
            </x-filament::button>

            <x-filament::button
                color="warning"
                icon="heroicon-o-arrow-path"
                wire:click="autorizarSustitucionesEIniciar"
                wire:loading.attr="disabled"
                wire:target="autorizarSustitucionesEIniciar"
            >
                Autorizar e iniciar
            </x-filament::button>
        </div>
    </x-filament::modal>

    <script>
        window.audioCtxKDS = window.audioCtxKDS || null;
        window.audioKDSEnabled = window.audioKDSEnabled || localStorage.getItem('kdsAudioEnabled') === '1';

        function toggleKDSFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }

        function obtenerAudioKDS() {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return null;

            if (!window.audioCtxKDS) {
                window.audioCtxKDS = new AudioContext();
            }

            return window.audioCtxKDS;
        }

        async function desbloquearAudioKDS() {
            try {
                const audioCtxKDS = obtenerAudioKDS();
                if (!audioCtxKDS) return;

                if (audioCtxKDS.state === 'suspended') {
                    await audioCtxKDS.resume();
                }

                window.audioKDSEnabled = true;
                localStorage.setItem('kdsAudioEnabled', '1');
                reproducirPulsoKDS(587.33, 0, 0.15, 0.15);
                hablarKDS('Audio activado');

                pintarEstadoAudioKDS();
            } catch (e) {
                console.error('Audio error:', e);
            }
        }

        function pintarEstadoAudioKDS() {
            window.audioKDSEnabled = localStorage.getItem('kdsAudioEnabled') === '1';
            const btn = document.getElementById('btn-activar-audio-kds');
            if (!btn || !window.audioKDSEnabled) return;

            btn.innerHTML = '<span class="text-[10px] sm:text-xs">Voz On</span>';
            btn.classList.remove('bg-primary-600', 'hover:bg-primary-500');
            btn.classList.add('bg-emerald-600', 'cursor-default');
        }

        function reproducirPulsoKDS(frecuencia, inicio, duracion, volumen = 0.5) {
            const audioCtxKDS = obtenerAudioKDS();
            if (!audioCtxKDS || audioCtxKDS.state !== 'running') return;

            const t = audioCtxKDS.currentTime;
            const osc = audioCtxKDS.createOscillator();
            const gain = audioCtxKDS.createGain();

            try {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(frecuencia, t + inicio);
                gain.gain.setValueAtTime(0, t + inicio);
                gain.gain.linearRampToValueAtTime(volumen, t + inicio + 0.03);
                gain.gain.exponentialRampToValueAtTime(0.001, t + inicio + duracion);
                osc.connect(gain);
                gain.connect(audioCtxKDS.destination);
                osc.start(t + inicio);
                osc.stop(t + inicio + duracion);
            } catch (e) {
                console.error('Audio error:', e);
            }
        }

        function reproducirAlertaKDS() {
            window.audioKDSEnabled = localStorage.getItem('kdsAudioEnabled') === '1';
            if (!window.audioKDSEnabled) return;
            const audioCtxKDS = obtenerAudioKDS();

            if (!audioCtxKDS || audioCtxKDS.state !== 'running') {
                pintarEstadoAudioKDS();
            } else {
                reproducirPulsoKDS(880, 0, 0.4);
                reproducirPulsoKDS(1318.51, 0.15, 0.6);
            }

            hablarKDS('Comanda nueva');
        }

        function hablarKDS(texto) {
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
                console.error('Speech error:', e);
            }
        }

        window.addEventListener('nuevo-pedido-audio', reproducirAlertaKDS);
        document.addEventListener('DOMContentLoaded', pintarEstadoAudioKDS);
        setInterval(pintarEstadoAudioKDS, 1000);
        document.addEventListener('click', () => {
            if (window.audioKDSEnabled) {
                obtenerAudioKDS()?.resume?.();
                pintarEstadoAudioKDS();
            }
        }, { once: false });

        document.addEventListener('livewire:init', () => {
            pintarEstadoAudioKDS();

            if (window.Livewire) {
                window.Livewire.on('nuevo-pedido-audio', reproducirAlertaKDS);
                window.Livewire.hook?.('morphed', pintarEstadoAudioKDS);
            }
        });

        document.addEventListener('livewire:initialized', () => {
            pintarEstadoAudioKDS();

            if (window.Livewire) {
                window.Livewire.on('nuevo-pedido-audio', reproducirAlertaKDS);
                window.Livewire.hook?.('morphed', pintarEstadoAudioKDS);
            }
        });
    </script>
</x-filament-panels::page>
