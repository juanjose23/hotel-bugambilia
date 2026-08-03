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

</x-filament-panels::page>
