@props([
    'areaSeleccionada' => null,
])

<div class="flex flex-wrap items-center justify-between gap-4 p-4 bg-slate-900 rounded-2xl border border-slate-800 text-white shadow-md">
    <div class="flex items-center gap-3">
        <div class="p-2.5 bg-[rgba(107,0,62,0.2)] text-[#e87faa] rounded-xl border border-[rgba(107,0,62,0.3)] shrink-0">
            <x-filament::icon icon="heroicon-o-fire" class="w-6 h-6" />
        </div>
        <div>
            <h2 class="text-sm font-bold uppercase tracking-wide text-white">Estaciones de Producción KDS</h2>
            <p class="text-xs text-slate-400">Filtrado por estación de preparación</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 text-xs">
        <button 
            wire:click="$set('areaSeleccionada', null)"
            class="px-3.5 py-1.5 rounded-xl font-bold uppercase transition-all text-xs border cursor-pointer {{ $areaSeleccionada === null ? 'bg-[#6b003e] text-white border-[#8a004e] shadow-sm' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700' }}"
        >
            Todas las Áreas
        </button>
        @foreach(\App\Enums\Restaurante\AreaCocina::cases() as $area)
            <button 
                wire:click="$set('areaSeleccionada', '{{ $area->value }}')"
                class="px-3.5 py-1.5 rounded-xl font-bold uppercase transition-all text-xs flex items-center gap-1.5 border cursor-pointer {{ $areaSeleccionada === $area->value ? 'bg-[#6b003e] text-white border-[#8a004e] shadow-sm' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700' }}"
            >
                <x-filament::icon :icon="$area->getIcon()" class="w-4 h-4" />
                {{ $area->getLabel() }}
            </button>
        @endforeach
    </div>
</div>
