@props([
    'consumos' => [],
    'variantes' => collect(),
])

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mt-1">
    @foreach($consumos as $id => $qty)
        @php
            $v = $variantes->get($id);
            $nombre = $v
                ? ($v->producto->nombre ?? '') . ($v->nombre_variante ? " ({$v->nombre_variante})" : '')
                : "Insumo #{$id}";
        @endphp
        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-250 dark:border-gray-800 shadow-sm">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 pr-2">{{ $nombre }}</span>
            <span class="px-2.5 py-0.5 text-xs font-bold bg-primary-100 dark:bg-primary-950 text-primary-800 dark:text-primary-300 rounded-full shrink-0">x{{ $qty }}</span>
        </div>
    @endforeach
</div>
