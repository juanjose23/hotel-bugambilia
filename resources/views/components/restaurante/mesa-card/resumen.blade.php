@props([
    'mesa',
    'simboloMonedas',
    'tienePedidos',
    'cantidadPedidos',
    'totalMesa',
])

<section class="mb-3 space-y-2" aria-label="Resumen de consumo">
    <div class="grid grid-cols-2 gap-2 rounded-xl border border-gray-200/80 bg-gray-50/80 p-2.5 dark:border-gray-800/80 dark:bg-gray-900/50">
        <div>
            <span class="block text-[9px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Comandas activas
            </span>
            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                {{ $cantidadPedidos }} {{ $cantidadPedidos === 1 ? 'abierta' : 'abiertas' }}
            </span>
        </div>

        <div class="border-l border-gray-200/80 pl-2.5 text-right dark:border-gray-800/80">
            <span class="block text-[9px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Total acumulado
            </span>
            <span class="font-mono text-sm font-black text-gray-950 dark:text-white">
                {{ $simboloMonedas }} {{ number_format($totalMesa, 2) }}
            </span>
        </div>
    </div>

    @if ($tienePedidos)
        <x-filament::button
            type="button"
            wire:click="verComandasMesa({{ $mesa->id }})"
            color="gray"
            size="xs"
            icon="heroicon-o-eye"
            class="w-full justify-between"
        >
            <span>Ver comanda completa</span>
            <span class="ml-1 rounded-md bg-gray-200/80 px-1.5 py-0.5 text-[10px] font-black dark:bg-gray-700">
                {{ $cantidadPedidos }}
            </span>
        </x-filament::button>
    @endif
</section>

