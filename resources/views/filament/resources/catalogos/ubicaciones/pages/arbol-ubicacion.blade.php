<x-filament-panels::page>
    <div class="fi-resource-tree-container space-y-6">
        <!-- Header -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="fi-section p-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900 transition-all duration-300">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-primary-50 dark:bg-primary-500/10">
                        <x-heroicon-o-building-office-2 class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Jerarquía</p>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Estructura Hotel</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tree Area -->
        <div class="fi-section rounded-2xl border border-gray-200 bg-gray-50/50 p-4 shadow-inner dark:border-white/5 dark:bg-gray-950/50 md:p-8 transition-all duration-300">
            <div class="max-w-4xl mx-auto">
                <div class="flex flex-col gap-4">
                    @forelse($this->getTreeData() as $node)
                        @include('filament.resources.catalogos.ubicaciones.pages.tree-node', ['node' => $node, 'level' => 0])
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <x-heroicon-o-map class="mb-4 h-12 w-12 text-gray-300 dark:text-gray-700" />
                            <p class="italic text-gray-500 dark:text-gray-400">No hay ubicaciones registradas.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <style>
        .fi-resource-tree-container {
            --primary-color: #711C37;
        }
    </style>
</x-filament-panels::page>
