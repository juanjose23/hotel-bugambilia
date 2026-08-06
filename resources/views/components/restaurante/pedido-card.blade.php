@props([
    'pedido',
    'modo' => 'kds', // 'kds', 'turno', 'publico'
    'tiempoTranscurrido' => '',
])

@php
    $numComanda = str_contains((string)$pedido->codigo, '-')
        ? ('#' . last(explode('-', (string)$pedido->codigo)))
        : ('#' . $pedido->codigo);

    $mesaNombre = $pedido->getRelation('mesa')?->nombre ?? 'Llevar / Domicilio';
    $minutosEspera = $pedido->created_at ? $pedido->created_at->diffInMinutes(now()) : 0;
    $slaNivel = match (true) {
        $minutosEspera > 20 => 'critico',
        $minutosEspera > 10 => 'alerta',
        default => 'normal',
    };
    $slaClases = match ($slaNivel) {
        'critico' => 'border-rose-500/80 dark:border-rose-500/70 bg-rose-50/95 dark:bg-rose-950/30 shadow-rose-900/20 animate-pulse',
        'alerta' => 'border-amber-400/80 dark:border-amber-500/70 bg-amber-50/95 dark:bg-amber-950/25 shadow-amber-900/10',
        default => 'border-emerald-300/80 dark:border-emerald-800 bg-white/95 dark:bg-gray-900/95',
    };
    $slaBarraClases = match ($slaNivel) {
        'critico' => 'bg-rose-600',
        'alerta' => 'bg-amber-500',
        default => 'bg-emerald-500',
    };
    $slaBadgeClases = match ($slaNivel) {
        'critico' => 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-950/70 dark:text-rose-200 dark:border-rose-800',
        'alerta' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-950/70 dark:text-amber-200 dark:border-amber-800',
        default => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-950/70 dark:text-emerald-200 dark:border-emerald-800',
    };
    $slaTexto = match ($slaNivel) {
        'critico' => 'Crítico',
        'alerta' => 'Atención',
        default => 'En tiempo',
    };
@endphp

@if($modo === 'publico')
    {{-- MODO PÚBLICO / TV CLIENTES --}}
    <div class="p-5 sm:p-6 rounded-3xl bg-slate-950/90 dark:bg-slate-900/95 border border-emerald-500/30 shadow-xl backdrop-blur-md flex items-center justify-between group hover:border-emerald-400 transition-all duration-300">
        <div class="space-y-1">
            <span class="text-3xl sm:text-5xl font-black font-mono tracking-tight text-white block bg-gradient-to-r from-white via-slate-100 to-emerald-400 bg-clip-text text-transparent">
                {{ $numComanda }}
            </span>
            <span class="text-xs sm:text-sm font-semibold text-emerald-400 flex items-center gap-1.5">
                <x-filament::icon icon="heroicon-o-table-cells" class="w-4 h-4 text-emerald-400/80" />
                {{ $mesaNombre }}
            </span>
        </div>
        <div class="p-3 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 shadow-inner group-hover:scale-110 transition-transform">
            <x-filament::icon icon="heroicon-o-check-circle" class="w-8 h-8 sm:w-10 sm:h-10" />
        </div>
    </div>
@else
    {{-- MODO KDS / COCINA & TURNO & DESPACHO --}}
    @php
        $estadoObj = $pedido->estado instanceof \App\Enums\Restaurante\EstadoPedido 
            ? $pedido->estado 
            : (is_string($pedido->estado) ? \App\Enums\Restaurante\EstadoPedido::tryFrom($pedido->estado) : null);
    @endphp

    <div dusk="kds-pedido-{{ $pedido->id }}" class="rounded-3xl border {{ $slaClases }} p-4 sm:p-6 shadow-lg backdrop-blur-md flex flex-col justify-between space-y-4 hover:shadow-xl transition-all duration-300 relative overflow-hidden group bg-white/95 dark:bg-gray-900/95">
        {{-- Barra superior de acento según estado --}}
        <div class="absolute top-0 left-0 right-0 h-1.5 {{ $slaBarraClases }}"></div>

        <div>
            {{-- Encabezado --}}
            <div class="flex items-start justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="font-black text-lg sm:text-xl text-gray-900 dark:text-white tracking-tight font-mono">
                        {{ $numComanda }}
                    </h3>
                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-2.5 py-1 rounded-xl">
                            {{ $mesaNombre }}
                        </span>
                        @if($tiempoTranscurrido)
                            <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 font-medium bg-gray-50 dark:bg-gray-800/60 px-2 py-0.5 rounded-lg">
                                <x-filament::icon icon="heroicon-o-clock" class="w-3.5 h-3.5 text-gray-400" />
                                {{ $tiempoTranscurrido }}
                            </span>
                        @endif
                        <span class="text-xs flex items-center gap-1 font-bold px-2 py-0.5 rounded-lg border {{ $slaBadgeClases }}">
                            {{ $slaTexto }} · {{ $minutosEspera }} min
                        </span>
                    </div>
                </div>

                <x-restaurante.estado-badge :estado="$estadoObj" />
            </div>

            {{-- Items del Pedido --}}
            <div class="space-y-2 mt-3 sm:mt-4">
                @foreach($pedido->items as $item)
                    @php
                        $estadoItemEnum = $item->estado instanceof \App\Enums\Restaurante\EstadoItemPedido
                            ? $item->estado
                            : (is_string($item->estado) ? \App\Enums\Restaurante\EstadoItemPedido::tryFrom($item->estado) : null);
                    @endphp
                    @if($estadoItemEnum !== \App\Enums\Restaurante\EstadoItemPedido::ANULADO)
                        <div class="flex items-start justify-between p-3 rounded-2xl bg-gray-50/80 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 hover:bg-gray-100/80 dark:hover:bg-gray-800/80 transition-colors">
                            <div class="flex items-start gap-3">
                                @if($estadoItemEnum === \App\Enums\Restaurante\EstadoItemPedido::SERVIDO)
                                    <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/30" title="Servido al cliente">
                                        <x-filament::icon icon="heroicon-o-check-badge" class="w-4 h-4" />
                                    </span>
                                @elseif($estadoItemEnum === \App\Enums\Restaurante\EstadoItemPedido::LISTO)
                                    <button
                                        dusk="kds-item-{{ $item->id }}-servido"
                                        type="button"
                                        wire:click="marcarItemServido({{ $item->id }})"
                                        title="Marcar como servido al cliente"
                                        class="w-6 h-6 rounded-full border border-emerald-400 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500 hover:text-white transition-colors shrink-0 flex items-center justify-center cursor-pointer mt-0.5 shadow-xs"
                                    >
                                        <x-filament::icon icon="heroicon-o-check-badge" class="w-4 h-4" />
                                    </button>
                                @elseif($estadoItemEnum === \App\Enums\Restaurante\EstadoItemPedido::EN_PREPARACION)
                                    <div class="flex items-center gap-1.5 shrink-0 mt-0.5">
                                        @if($modo === 'kds')
                                    <button
                                        dusk="kds-item-{{ $item->id }}-listo"
                                        type="button"
                                        wire:click="marcarItemListo({{ $item->id }})"
                                        title="Marcar como listo para servir"
                                                class="w-6 h-6 rounded-full border border-emerald-500/60 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center cursor-pointer shadow-xs"
                                            >
                                                <x-filament::icon icon="heroicon-o-check" class="w-3.5 h-3.5" />
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="anularItemPedido({{ $item->id }})"
                                                title="Anular plato"
                                                wire:confirm="¿Anular este plato?"
                                                class="w-6 h-6 rounded-full border border-rose-400/60 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center cursor-pointer shadow-xs"
                                            >
                                                <x-filament::icon icon="heroicon-o-x-mark" class="w-3.5 h-3.5" />
                                            </button>
                                        @else
                                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse shrink-0"></span>
                                        @endif
                                    </div>
                                @else
                                    <span class="w-6 h-6 rounded-full border border-gray-300 dark:border-gray-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <x-filament::icon icon="heroicon-o-clock" class="w-3.5 h-3.5 text-gray-400" />
                                    </span>
                                @endif

                                <div class="flex flex-col">
                                    <span class="font-bold text-xs sm:text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item->getRelation('plato')?->nombre ?? 'Plato General' }}
                                    </span>
                                    @if($item->observaciones)
                                        <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-0.5 bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 rounded-md border border-rose-200/50 dark:border-rose-800/40 w-fit">
                                            {{ $item->observaciones }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <span class="text-xs font-black text-gray-800 dark:text-gray-200 bg-gray-200/80 dark:bg-gray-700/80 px-2.5 py-1 rounded-xl shrink-0 border border-gray-300/50 dark:border-gray-600/50">
                                ×{{ (int) $item->cantidad }}
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Acciones --}}
        <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2">
            @if($modo === 'turno')
                <button
                    dusk="kds-pedido-{{ $pedido->id }}-cobrar"
                    type="button"
                    wire:click="iniciarCobroPedido({{ $pedido->id }})"
                    wire:loading.attr="disabled"
                    wire:target="iniciarCobroPedido({{ $pedido->id }})"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-xs sm:text-sm font-black py-3 px-4 shadow-md hover:shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/40 disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer"
                >
                    <x-filament::icon icon="heroicon-o-banknotes" class="w-4 h-4" />
                    <span wire:loading.remove wire:target="iniciarCobroPedido({{ $pedido->id }})">Cobrar / Pagar Cuenta</span>
                    <span wire:loading wire:target="iniciarCobroPedido({{ $pedido->id }})">Procesando…</span>
                </button>
            @else
                @if($pedido->estado === \App\Enums\Restaurante\EstadoPedido::ABIERTO)
                    <x-filament::button
                        dusk="kds-pedido-{{ $pedido->id }}-preparar"
                        wire:click="prepararAlimento({{ $pedido->id }})"
                        color="warning"
                        icon="heroicon-o-fire"
                        size="sm"
                        class="w-full sm:w-auto text-xs font-bold"
                    >
                        Preparar
                    </x-filament::button>
                @else
                    <span class="hidden sm:block"></span>
                @endif

                <x-filament::button
                    tag="a"
                    href="{{ route('admin.restaurante.comanda', ['pedido' => $pedido->id]) }}"
                    target="_blank"
                    color="gray"
                    icon="heroicon-o-printer"
                    size="sm"
                    class="w-full sm:w-auto text-xs font-bold"
                >
                    Imprimir Comanda
                </x-filament::button>
            @endif
        </div>
    </div>
@endif
