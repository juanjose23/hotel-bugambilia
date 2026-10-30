<x-filament-panels::page>
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Accede a tus cobros, documentos y controles de caja desde un solo lugar.
    </p>

    <div data-guia="facturacion-accesos" class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        @foreach ($this->gruposDeAcceso() as $grupo)
            @if (count($grupo->getItems()))
                <x-filament::section :heading="$grupo->getLabel()">
                    <nav aria-label="{{ $grupo->getLabel() }}" class="space-y-1">
                        @foreach ($grupo->getItems() as $opcion)
                            <a href="{{ $opcion->getUrl() }}" wire:navigate
                                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-gray-700 transition hover:bg-primary-50 hover:text-primary-700 focus-visible:outline-2 focus-visible:outline-primary-500 dark:text-gray-200 dark:hover:bg-white/5 dark:hover:text-primary-300">
                                @if ($opcion->getIcon())
                                    <x-filament::icon :icon="$opcion->getIcon()" class="h-5 w-5 shrink-0 text-gray-400" />
                                @endif
                                <span class="min-w-0 flex-1">{{ $opcion->getLabel() }}</span>
                                <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4 shrink-0 text-gray-400" />
                            </a>
                        @endforeach
                    </nav>
                </x-filament::section>
            @endif
        @endforeach
    </div>
</x-filament-panels::page>
