@props([
    'monto' => 0,
    'moneda' => 'C$',
    'titular' => 'Huésped',
])

<div
    x-data="{
        simulando: false,
        procesando: false,
        aprobado: false,
        tipoLectura: 'contactless',
        sonidoPos: () => {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 880;
                gain.gain.value = 0.08;
                osc.start();
                setTimeout(() => { osc.stop(); }, 120);
            } catch (e) {}
        },
        sonidoExito: () => {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const now = ctx.currentTime;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                gain.gain.value = 0.08;
                osc.frequency.setValueAtTime(587.33, now); // D5
                osc.frequency.setValueAtTime(880, now + 0.1); // A5
                osc.start(now);
                osc.stop(now + 0.25);
            } catch (e) {}
        },
        aplicarTarjeta(numero, expMes, expAnio, cvc, titular) {
            this.sonidoPos();
            this.simulando = true;
            $wire.set('mountedActionsData.0.numero_tarjeta', numero);
            $wire.set('mountedActionsData.0.exp_mes', expMes);
            $wire.set('mountedActionsData.0.exp_anio', expAnio);
            $wire.set('mountedActionsData.0.cvc', cvc);
            if (titular) {
                $wire.set('mountedActionsData.0.titular', titular);
            }
            setTimeout(() => { this.simulando = false; }, 800);
        },
        teclear(digito) {
            this.sonidoPos();
            const actual = $wire.get('mountedActionsData.0.numero_tarjeta') || '';
            if (actual.replace(/\D/g, '').length < 16) {
                const limpio = actual.replace(/\D/g, '') + digito;
                // Formatear en bloques de 4
                const formateado = limpio.match(/.{1,4}/g)?.join(' ') || limpio;
                $wire.set('mountedActionsData.0.numero_tarjeta', formateado);
            }
        },
        borrarDigito() {
            this.sonidoPos();
            const actual = $wire.get('mountedActionsData.0.numero_tarjeta') || '';
            const limpio = actual.replace(/\D/g, '');
            if (limpio.length > 0) {
                const nuevo = limpio.slice(0, -1);
                const formateado = nuevo.match(/.{1,4}/g)?.join(' ') || nuevo;
                $wire.set('mountedActionsData.0.numero_tarjeta', formateado);
            }
        },
        abrirEscanerCamara() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.capture = 'environment';
            input.onchange = (e) => {
                const file = e.target.files?.[0];
                if (file) {
                    this.simularLecturaContactless();
                }
            };
            input.click();
        },
        simularLecturaContactless() {
            this.tipoLectura = 'contactless';
            this.aplicarTarjeta('4242 4242 4242 4242', '12', '2028', '123', '{{ addslashes($titular) }}');
        },
        simularTarjetaMastercard() {
            this.tipoLectura = 'chip';
            this.aplicarTarjeta('5555 5555 5555 4444', '10', '2027', '888', '{{ addslashes($titular) }}');
        },
        simularTarjetaDeclinada() {
            this.tipoLectura = 'chip';
            this.aplicarTarjeta('4000 0000 0000 0002', '05', '2027', '999', '{{ addslashes($titular) }}');
        }
    }"
    class="w-full bg-slate-900 text-slate-100 rounded-3xl p-5 shadow-2xl border border-slate-700/80 mb-4 select-none"
>
    {{-- Header del Datáfono: Marca y luces contactless --}}
    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
            <span class="text-[11px] font-black tracking-widest uppercase text-slate-300">TERMINAL POS STRIPE • HOTEL BUGAMBILIAS</span>
        </div>
        {{-- Luces LED Contactless --}}
        <div class="flex items-center gap-1.5 bg-slate-950/60 px-2.5 py-1 rounded-full border border-slate-800">
            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></div>
            <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
            <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
            <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
            <span class="text-[9px] font-bold text-emerald-400 ml-1">NFC LISTO</span>
        </div>
    </div>

    {{-- Pantalla LCD del Datáfono --}}
    <div class="bg-gradient-to-b from-slate-950 to-blue-950/60 rounded-2xl p-4 border border-blue-900/40 shadow-inner mb-4 relative overflow-hidden">
        {{-- Glow de fondo --}}
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex items-center justify-between text-[11px] text-blue-300 font-mono mb-2">
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-blue-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                EN LÍNEA (4G / STRIPE)
            </span>
            <span class="text-slate-400 font-bold">TERMINAL #POS-01</span>
        </div>

        <div class="text-center py-2">
            <div class="text-xs uppercase tracking-wider font-semibold text-slate-400">Total a Liquidar en POS</div>
            <div class="text-3xl font-black text-white font-mono tracking-tight mt-1">
                <span class="text-emerald-400">{{ $moneda }}</span>
                <span>{{ number_format((float) $monto, 2) }}</span>
            </div>
            <p class="text-[11px] text-emerald-400/90 font-medium mt-1 flex items-center justify-center gap-1">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                Aproxime tarjeta contactless, inserte chip o escanee
            </p>
        </div>
    </div>

    {{-- Tarjeta de Crédito Visual 3D Interactiva --}}
    <div
        :class="{ 'scale-[1.02] ring-2 ring-emerald-400': simulando }"
        class="relative transition-all duration-300 bg-gradient-to-tr from-slate-800 via-slate-900 to-indigo-950 rounded-2xl p-4 border border-slate-700 shadow-xl mb-4 overflow-hidden"
    >
        {{-- Patrón de fondo holográfico --}}
        <div class="absolute inset-0 bg-radial from-white/5 to-transparent pointer-events-none"></div>

        <div class="flex justify-between items-start mb-3 relative z-10">
            <div class="flex items-center gap-2">
                {{-- Chip EMV dorado --}}
                <div class="w-9 h-7 bg-gradient-to-br from-amber-300 via-amber-400 to-amber-600 rounded-md border border-amber-200/50 shadow-xs flex items-center justify-center">
                    <div class="w-6 h-4 border border-amber-800/40 rounded-xs"></div>
                </div>
                {{-- Contactless Waves --}}
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <div class="text-right">
                <span class="text-xs font-black italic tracking-widest text-slate-300">VISA / MASTERCARD</span>
                <div class="text-[9px] text-slate-400 font-mono">CRÉDITO / DÉBITO</div>
            </div>
        </div>

        {{-- Número visible en tarjeta --}}
        <div class="relative z-10 mb-2">
            <div
                class="font-mono text-base md:text-lg tracking-widest text-slate-100 font-bold"
                x-text="$wire.get('mountedActionsData.0.numero_tarjeta') || '•••• •••• •••• ••••'"
            ></div>
        </div>

        <div class="flex justify-between items-end text-xs relative z-10 font-mono">
            <div>
                <div class="text-[9px] uppercase tracking-wider text-slate-400">Titular</div>
                <div
                    class="font-bold text-slate-200 uppercase truncate max-w-[180px]"
                    x-text="$wire.get('mountedActionsData.0.titular') || '{{ addslashes($titular) }}'"
                ></div>
            </div>
            <div class="text-right">
                <div class="text-[9px] uppercase tracking-wider text-slate-400">Vence</div>
                <div class="font-bold text-slate-200">
                    <span x-text="$wire.get('mountedActionsData.0.exp_mes') || '12'"></span>
                    /
                    <span x-text="($wire.get('mountedActionsData.0.exp_anio') || '2028').toString().slice(-2)"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Botonera de Simulación Rápida de Datáfono --}}
    <div class="space-y-2 mb-4">
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Acciones del Datáfono / Lector</span>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <button
                type="button"
                @click="simularLecturaContactless()"
                class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-emerald-600/30 hover:bg-emerald-600/50 border border-emerald-500/40 text-emerald-300 font-bold text-xs transition-all active:scale-95"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Tap Contactless
            </button>

            <button
                type="button"
                @click="simularTarjetaMastercard()"
                class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-blue-600/30 hover:bg-blue-600/50 border border-blue-500/40 text-blue-300 font-bold text-xs transition-all active:scale-95"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Insertar Chip MC
            </button>

            <button
                type="button"
                @click="abrirEscanerCamara()"
                class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-purple-600/30 hover:bg-purple-600/50 border border-purple-500/40 text-purple-300 font-bold text-xs transition-all active:scale-95 col-span-2 sm:col-span-1"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Escanear Tarjeta
            </button>
        </div>
    </div>

    {{-- Teclado Numérico Físico del Datáfono (Keypad estilo POS terminal) --}}
    <div class="bg-slate-950/70 rounded-2xl p-3 border border-slate-800">
        <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold uppercase mb-2 px-1">
            <span>Teclado Numérico POS</span>
            <span class="text-slate-500">Digitar o Corregir</span>
        </div>
        <div class="grid grid-cols-3 gap-1.5 font-mono">
            <template x-for="n in [1, 2, 3, 4, 5, 6, 7, 8, 9]" :key="n">
                <button
                    type="button"
                    @click="teclear(n)"
                    x-text="n"
                    class="py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-sm text-center transition-all active:scale-95 border border-slate-700/60 shadow-xs"
                ></button>
            </template>
            {{-- Fila inferior: Cancelar/Borrar, 0, Limpiar --}}
            <button
                type="button"
                @click="borrarDigito()"
                class="py-2.5 rounded-lg bg-amber-900/40 hover:bg-amber-900/60 text-amber-300 font-bold text-xs text-center transition-all active:scale-95 border border-amber-800/60"
            >
                CORR
            </button>
            <button
                type="button"
                @click="teclear(0)"
                class="py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-sm text-center transition-all active:scale-95 border border-slate-700/60 shadow-xs"
            >
                0
            </button>
            <button
                type="button"
                @click="$wire.set('mountedActionsData.0.numero_tarjeta', ''); sonidoPos();"
                class="py-2.5 rounded-lg bg-red-900/40 hover:bg-red-900/60 text-red-300 font-bold text-xs text-center transition-all active:scale-95 border border-red-800/60"
            >
                ANULAR
            </button>
        </div>
    </div>
</div>
