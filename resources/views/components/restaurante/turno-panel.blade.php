@props([
    'titulo' => '',
    'subtitulo' => '',
    'icon' => 'heroicon-o-clock',
    'badgeText' => '',
    'badgeColor' => 'primary',
    'pedidos' => collect(),
    'modo' => 'turno',
    'emptyIcon' => 'heroicon-o-check-circle',
    'emptyText' => 'Sin órdenes.',
    'componenteRef' => null,
])

<div class="bg-slate-900 rounded-2xl p-6 border border-slate-800 shadow-xl flex flex-col justify-between space-y-6">
    <div>
        {{-- Encabezado del Panel --}}
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-slate-800 border border-slate-700 rounded-xl text-slate-300">
                    <x-filament::icon :icon="$icon" class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="text-lg font-extrabold uppercase tracking-wide text-white">
                        {{ $titulo }}
                    </h2>
                    @if($subtitulo)
                        <p class="text-xs text-slate-400 font-medium">{{ $subtitulo }}</p>
                    @endif
                </div>
            </div>
            <x-filament::badge :color="$badgeColor" size="sm">
                {{ $badgeText }}
            </x-filament::badge>
        </div>

        {{-- Grid o Lista de Pedidos --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
            @forelse($pedidos as $pedido)
                <x-restaurante.pedido-card 
                    :pedido="$pedido"
                    :modo="$modo"
                    :tiempoTranscurrido="$componenteRef ? $componenteRef->tiempoTranscurrido($pedido) : ''"
                />
            @empty
                <div class="col-span-full text-center py-12 text-slate-500">
                    <x-filament::icon :icon="$emptyIcon" class="w-10 h-10 text-slate-600 mx-auto mb-2" />
                    <p class="text-xs font-medium">{{ $emptyText }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
