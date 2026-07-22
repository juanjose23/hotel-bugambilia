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
            @endphp
            
            <div class="rounded-3xl border-2 p-5 shadow-sm transition-all duration-300 hover:shadow-md flex flex-col justify-between {{ $cardBorderClass }}">
                <div>
                    {{-- Card Header --}}
                    <div class="flex items-start justify-between mb-4 pb-3 border-b border-gray-200/50 dark:border-gray-800/50">
                        <div>
                            <h3 class="font-black text-xl text-gray-900 dark:text-white tracking-tight">{{ $pedido->codigo }}</h3>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-lg">
                                    {{ $pedido->mesa->nombre ?? 'Llevar / Domicilio' }}
                                </span>
                                <span class="text-xs font-extrabold flex items-center gap-1 {{ $timerTextClass }}">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Hace {{ $this->tiempoTranscurrido($pedido) }}
                                </span>
                            </div>
                        </div>
                        
                        <span @class([
                            'px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider',
                            'bg-amber-100 text-amber-800 dark:bg-amber-900/35 dark:text-amber-400' => $pedido->estado === 'abierto',
                            'bg-blue-100 text-blue-800 dark:bg-blue-900/35 dark:text-blue-400' => $pedido->estado === 'preparacion',
                            'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/35 dark:text-emerald-400' => $pedido->estado === 'listo',
                        ])>
                            {{ \App\Enums\Restaurante\EstadoPedido::from($pedido->estado)->label() }}
                        </span>
                    </div>

                    {{-- Items List --}}
                    <div class="space-y-3 mb-6">
                        @foreach($pedido->items as $item)
                            @if($item->estado !== 'cancelado')
                                <div class="flex items-start justify-between p-3 rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800/80 shadow-sm transition-all">
                                    <div class="flex items-start gap-3">
                                        {{-- Ready check button --}}
                                        @if($item->estado === 'listo')
                                            <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0">
                                                ✓
                                            </span>
                                        @else
                                            <button
                                                wire:click="marcarItemListo({{ $item->id }})"
                                                title="Marcar como preparado"
                                                class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-700 hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 transition-all shrink-0 flex items-center justify-center cursor-pointer"
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
                
                {{-- Action Button Bar --}}
                <div class="pt-3 border-t border-gray-200/50 dark:border-gray-800/50 flex items-center justify-end">
                    <a 
                        href="{{ route('restaurante.comanda', $pedido) }}" 
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-bugambilia-600 hover:bg-bugambilia-700 text-white rounded-2xl text-xs font-extrabold tracking-wide shadow-sm hover:shadow transition-all duration-200 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.617 0-1.11-.497-1.12-1.115L6.34 18m11.32 0H6.34m9.4-4.171c.068-1.548-.109-3.108-.528-4.606a2.122 2.122 0 0 0-1.92-1.558 48.243 48.243 0 0 0-5.66 0 2.122 2.122 0 0 0-1.92 1.558c-.42 1.498-.597 3.058-.528 4.606m8.568 0h-8.568m8.568 0c-.24.03-.48.062-.72.096H8.01m0 0c-.24-.03-.48-.062-.72-.096H6.34" />
                        </svg>
                        Imprimir Comanda
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20 bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800/80 max-w-md mx-auto p-6 shadow-sm">
                <svg class="w-16 h-16 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <h3 class="text-lg font-black text-gray-950 dark:text-white mb-1">Sin pedidos pendientes</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">La cocina está al día. Todos los pedidos se encuentran preparados.</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
