@props([
    'pedido',
    'modo' => 'kds', // 'kds', 'turno', 'publico'
    'tiempoTranscurrido' => '',
])

@php
    $numComanda = str_contains((string)$pedido->codigo, '-')
        ? ('#' . last(explode('-', (string)$pedido->codigo)))
        : ('#' . $pedido->codigo);

    $mesaNombre = $pedido->mesa->nombre ?? 'Llevar / Domicilio';
@endphp

@if($modo === 'publico')
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-md flex items-center justify-between">
        <div>
            <span class="text-4xl lg:text-5xl font-black font-mono tracking-tight text-white block">
                {{ $numComanda }}
            </span>
            <span class="text-xs font-semibold text-slate-400 mt-1 flex items-center gap-1">
                <x-filament::icon icon="heroicon-o-table-cells" class="w-3.5 h-3.5 text-slate-500" />
                {{ $mesaNombre }}
            </span>
        </div>
        <x-filament::icon icon="heroicon-o-check-circle" class="w-8 h-8 text-emerald-400 shrink-0" />
    </div>
@elseif($modo === 'turno')
    <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800 shadow-sm flex flex-col justify-between space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-2xl font-black font-mono text-white tracking-tight">
                {{ $numComanda }}
            </span>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 border border-slate-700">
                {{ $mesaNombre }}
            </span>
        </div>

        @if($tiempoTranscurrido)
            <div class="text-xs text-slate-400 flex items-center gap-1 font-medium">
                <x-filament::icon icon="heroicon-o-clock" class="w-3.5 h-3.5 text-slate-500" />
                Hace {{ $tiempoTranscurrido }}
            </div>
        @endif

        <div class="space-y-1 text-xs text-slate-300 border-t border-slate-800 pt-2">
            @foreach($pedido->items->take(3) as $item)
                <div class="flex justify-between items-center">
                    <span class="truncate max-w-[160px] text-slate-300 font-medium">{{ $item->plato->nombre ?? 'Platillo' }}</span>
                    <span class="font-bold text-slate-400">×{{ (int) $item->cantidad }}</span>
                </div>
            @endforeach
            @if($pedido->items->count() > 3)
                <span class="text-[10px] text-slate-500 font-semibold block mt-0.5">+ {{ $pedido->items->count() - 3 }} platillos más</span>
            @endif
        </div>
    </div>
@else
    {{-- MODO KDS --}}
    @php
        $estadoObj = $pedido->estado instanceof \App\Enums\Restaurante\EstadoPedido 
            ? $pedido->estado 
            : (is_string($pedido->estado) ? \App\Enums\Restaurante\EstadoPedido::tryFrom($pedido->estado) : null);
    @endphp

    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-xs flex flex-col justify-between space-y-4">
        <div>
            {{-- Encabezado --}}
            <div class="flex items-start justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="font-black text-lg text-gray-900 dark:text-white tracking-tight">{{ $pedido->codigo }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md">
                            {{ $mesaNombre }}
                        </span>
                        @if($tiempoTranscurrido)
                            <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 font-medium">
                                <x-filament::icon icon="heroicon-o-clock" class="w-3.5 h-3.5 text-gray-400" />
                                Hace {{ $tiempoTranscurrido }}
                            </span>
                        @endif
                    </div>
                </div>

                <x-restaurante.estado-badge :estado="$estadoObj" />
            </div>

            {{-- Items --}}
            <div class="space-y-2 mt-3">
                @foreach($pedido->items as $item)
                    @if($item->estado?->value !== 'cancelado')
                        <div class="flex items-start justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <div class="flex items-start gap-2.5">
                                @if($item->estado?->value === 'listo')
                                    <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                        <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4" />
                                    </span>
                                @elseif($item->estado?->value === 'en_preparacion')
                                    <button
                                        type="button"
                                        wire:click="marcarItemListo({{ $item->id }})"
                                        title="Marcar como preparado"
                                        class="w-6 h-6 rounded-full border border-gray-300 dark:border-gray-600 hover:bg-emerald-500 hover:border-emerald-500 hover:text-white transition-colors shrink-0 flex items-center justify-center cursor-pointer mt-0.5"
                                    >
                                        <x-filament::icon icon="heroicon-o-check" class="w-3.5 h-3.5" />
                                    </button>
                                @else
                                    <span class="w-6 h-6 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <x-filament::icon icon="heroicon-o-clock" class="w-3.5 h-3.5 text-gray-400" />
                                    </span>
                                @endif

                                <div class="flex flex-col">
                                    <span class="font-bold text-xs text-gray-900 dark:text-gray-100">
                                        {{ $item->plato->nombre ?? 'Plato General' }}
                                    </span>
                                    @if($item->observaciones)
                                        <span class="text-[11px] text-[#6b003e] dark:text-[#e87faa] font-semibold mt-0.5">
                                            Obs: {{ $item->observaciones }}
                                        </span>
                                    @elseif($item->notas)
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400 italic mt-0.5">
                                            Nota: {{ $item->notas }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <span class="text-xs font-black text-gray-800 dark:text-gray-200 bg-gray-200 dark:bg-gray-700 px-2 py-0.5 rounded-md shrink-0">
                                ×{{ (int) $item->cantidad }}
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Acciones --}}
        <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
            @if($pedido->estado->value === 'abierto')
                <x-filament::button
                    wire:click="prepararAlimento({{ $pedido->id }})"
                    color="warning"
                    icon="heroicon-o-fire"
                    size="sm"
                >
                    Preparar
                </x-filament::button>
            @else
                <span></span>
            @endif

            <x-filament::button
                tag="a"
                href="{{ route('admin.restaurante.comanda', ['pedido' => $pedido->id]) }}"
                target="_blank"
                color="gray"
                icon="heroicon-o-printer"
                size="sm"
            >
                Imprimir Comanda
            </x-filament::button>
        </div>
    </div>
@endif
