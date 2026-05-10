<div x-data="{ expanded: true }" class="relative" style="margin-left: {{ $level > 0 ? '2rem' : '0' }}">
    <!-- Connector Line -->
    @if ($level > 0)
        <div class="absolute -left-6 top-5 h-px w-6 bg-gray-200 dark:bg-white/10"></div>
    @endif

    <div class="group flex items-center gap-4 py-2">
        <!-- Icon & Toggle -->
        <div class="relative flex items-center justify-center">
            <button @click="expanded = !expanded"
                class="relative z-10 flex h-10 w-10 items-center justify-center rounded-xl transition-all duration-300"
                :class="expanded
                    ?
                    'bg-white shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rotate-0' :
                    'bg-gray-100 dark:bg-gray-900/50 border border-transparent dark:border-gray-800 -rotate-90'">
                @if ($node['tipo'] === 'edificio')
                    <x-heroicon-o-building-office class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                @elseif($node['tipo'] === 'piso')
                    <x-heroicon-o-squares-2x2 class="h-5 w-5 text-sky-600 dark:text-sky-400" />
                @elseif($node['tipo'] === 'sector')
                    <x-heroicon-o-squares-2x2 class="h-5 w-5 text-violet-600 dark:text-violet-400" />
                @else
                    <x-heroicon-o-map-pin class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                @endif
            </button>

            @if (count($node['children']) > 0)
                <div class="absolute -bottom-2 w-px bg-linear-to-b from-gray-200 to-transparent dark:from-white/10 transition-all duration-500"
                    :class="expanded ? 'h-full opacity-100' : 'h-0 opacity-0'"></div>
            @endif
        </div>

        <!-- Node Content Card -->
        <div class="flex-1 flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-6 py-3 shadow-sm transition-all duration-300 hover:border-primary-500/30 hover:bg-gray-50 dark:border-white/5 dark:bg-gray-900 dark:shadow-none dark:hover:bg-gray-800 cursor-pointer"
            @click="window.location.href = '{{ \App\Filament\Resources\Catalogos\Ubicacions\UbicacionResource::getUrl('view', ['record' => $node['id']]) }}'">
            <div class="flex items-center gap-4">
                <div>
                    <h4
                        class="text-sm font-bold text-gray-900 transition-colors group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                        {{ $node['nombre'] }}
                    </h4>
                    <p class="text-[10px] font-medium uppercase tracking-widest text-gray-500">
                        {{ $node['tipo'] }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 text-gray-400">
                @if (count($node['children']) > 0)
                    <span
                        class="rounded-md border border-gray-200 bg-gray-100 px-2 py-1 text-[10px] font-bold text-gray-500 dark:border-white/5 dark:bg-white/5 dark:text-gray-400">
                        {{ count($node['children']) }} Sub
                    </span>
                @endif
                <x-heroicon-m-chevron-right
                    class="h-4 w-4 transition-all transform group-hover:translate-x-1 group-hover:text-primary-600 dark:group-hover:text-primary-400" />
            </div>
        </div>
    </div>

    <!-- Children container -->
    <div x-show="expanded" x-collapse x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        class="ml-5 border-l border-gray-200 dark:border-white/10">
        @foreach ($node['children'] as $child)
            @include('filament.resources.catalogos.ubicacions.pages.tree-node', [
                'node' => $child,
                'level' => $level + 1,
            ])
        @endforeach
    </div>
</div>
