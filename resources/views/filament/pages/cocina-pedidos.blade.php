<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 font-sans">
        @forelse($this->pedidos as $pedido)
            @php
                $minutesElapsed = $pedido->created_at?->diffInMinutes(now()) ?? 0;
                $cardBorderClass = 'border-amber-500/40 bg-amber-50/50 dark:bg-amber-950/10 shadow-amber-100/20 dark:shadow-none';
                $timerTextClass = 'text-amber-700 dark:text-amber-400';
                
                if ($minutesElapsed >= 10 && $minutesElapsed < 20) {
                    $cardBorderClass = 'border-orange-500/40 bg-orange-50/50 dark:bg-orange-950/10 shadow-orange-100/20 dark:shadow-none';
                    $timerTextClass = 'text-orange-700 dark:text-orange-400';
                } elseif ($minutesElapsed >= 20) {
                    $cardBorderClass = 'border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/10 shadow-rose-100/20 dark:shadow-none';
                    $timerTextClass = 'text-rose-700 dark:text-rose-400';
                }

                $estadoObj = $pedido->estado instanceof \App\Enums\Restaurante\EstadoPedido 
                    ? $pedido->estado 
                    : (is_string($pedido->estado) ? \App\Enums\Restaurante\EstadoPedido::tryFrom($pedido->estado) : null);
            @endphp
            
            <div class="rounded-3xl border-2 p-5 shadow-sm transition-all duration-300 hover:shadow-md flex flex-col justify-between {{ $cardBorderClass }}">
                <div>
                    {{-- Encabezado de la Tarjeta de Pedido --}}
                    <div class="flex items-start justify-between mb-4 pb-3 border-b border-gray-200/50 dark:border-gray-800/50">
                        <div>
                            <h3 class="font-black text-xl text-gray-900 dark:text-white tracking-tight">{{ $pedido->codigo }}</h3>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="text-xs font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-lg">
                                    {{ $pedido->mesa->nombre ?? 'Llevar / Domicilio' }}
                                </span>
                                <span class="text-xs font-extrabold flex items-center gap-1 {{ $timerTextClass }}">
                                    <x-filament::icon icon="heroicon-o-clock" class="w-3.5 h-3.5" />
                                    Hace {{ $this->tiempoTranscurrido($pedido) }}
                                </span>
                            </div>
                        </div>
                        
                        <x-filament::badge color="{{ $estadoObj?->getColor() ?? 'gray' }}" size="sm">
                            {{ $estadoObj?->getLabel() ?? 'Pendiente' }}
                        </x-filament::badge>
                    </div>

                    {{-- Lista de Platos / Items --}}
                    <div class="space-y-3 mb-6">
                        @foreach($pedido->items as $item)
                            @if($item->estado !== 'cancelado')
                                <div class="flex items-start justify-between p-3 rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800/80 shadow-sm transition-all">
                                    <div class="flex items-start gap-3">
                                        {{-- Indicador o Botón de Preparado --}}
                                        @if($item->estado === 'listo')
                                            <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                                <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                            </span>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="marcarItemListo({{ $item->id }})"
                                                title="Marcar como preparado"
                                                aria-label="Marcar {{ $item->plato->nombre ?? 'Plato' }} como preparado"
                                                className="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-700 hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 transition-all shrink-0 flex items-center justify-center cursor-pointer"
                                            >
                                                <span class="w-2.5 h-2.5 rounded-full bg-transparent hover:bg-emerald-500 transition-colors"></span>
                                            </button>
                                        @endif
                                        
                                        <div class="flex flex-col">
                                            <span class="font-extrabold text-sm text-gray-900 dark:text-gray-100 leading-tight">
                                                {{ $item->plato->nombre ?? 'Plato General' }}
                                            </span>
                                            @if($item->notas)
                                                <span class="text-xs text-amber-600 dark:text-amber-400 font-medium italic mt-1 bg-amber-50 dark:bg-amber-950/30 px-2 py-0.5 rounded-lg border border-amber-500/10 w-fit">
                                                    Nota: {{ $item->notas }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <span class="text-xs font-black text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-lg shrink-0">
                                        ×{{ $item->cantidad }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                
                {{-- Botón de Acción Comanda --}}
                <div class="pt-3 border-t border-gray-200/50 dark:border-gray-800/50 flex items-center justify-end">
                    <x-filament::button
                        tag="a"
                        href="{{ route('admin.restaurante.comanda', $pedido) }}"
                        target="_blank"
                        color="warning"
                        icon="heroicon-o-printer"
                        size="sm"
                        aria-label="Imprimir comanda para pedido {{ $pedido->codigo }}"
                    >
                        Imprimir Comanda
                    </x-filament::button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20 bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800/80 max-w-md mx-auto p-6 shadow-sm">
                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-full w-fit mx-auto mb-4">
                    <x-filament::icon icon="heroicon-o-check-badge" class="w-10 h-10 text-emerald-500" />
                </div>
                <h3 class="text-lg font-black text-gray-950 dark:text-white mb-1">Sin pedidos pendientes</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">La cocina está al día. Todos los pedidos se encuentran preparados.</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
