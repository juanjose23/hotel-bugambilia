<x-filament-panels::page>
    <div class="space-y-4 lg:space-y-0 lg:flex lg:gap-6 lg:items-start">
        
        <!-- NAVEGACIÓN MOBILE & TABLET (Pantallas pequeñas y medianas) -->
        <nav class="lg:hidden w-full overflow-x-auto pb-1 -mx-4 px-4 sm:mx-0 sm:px-0">
            <div class="flex items-center gap-2 p-1.5 bg-gray-100/90 dark:bg-gray-900/90 border border-gray-200 dark:border-gray-800 rounded-xl min-w-max">
                <!-- Tab: Inventario -->
                <button type="button" wire:click="$set('activeTab', 'inventario')"
                    class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150 shrink-0 {{ $activeTab === 'inventario' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-filament::icon icon="heroicon-m-archive-box" class="w-4 h-4" />
                    <span>Inventario</span>
                </button>

                <!-- Tab: Entrada de Blancos -->
                <button type="button" wire:click="$set('activeTab', 'entrada')"
                    class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150 shrink-0 {{ $activeTab === 'entrada' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-filament::icon icon="heroicon-m-arrow-down-tray" class="w-4 h-4" />
                    <span>Entrada Blancos</span>
                </button>

                <!-- Tab: Entrada de Insumos Químicos -->
                <button type="button" wire:click="$set('activeTab', 'entrada_insumos')"
                    class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150 shrink-0 {{ $activeTab === 'entrada_insumos' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-filament::icon icon="heroicon-m-plus-circle" class="w-4 h-4" />
                    <span>Entrada Insumos</span>
                </button>

                <!-- Tab: Consumo por Jornada -->
                <button type="button" wire:click="$set('activeTab', 'jornada')"
                    class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150 shrink-0 {{ $activeTab === 'jornada' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-filament::icon icon="heroicon-m-sparkles" class="w-4 h-4" />
                    <span>Consumo Jornada</span>
                </button>

                <!-- Tab: Mermas y Bajas -->
                <button type="button" wire:click="$set('activeTab', 'consumir')"
                    class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150 shrink-0 {{ $activeTab === 'consumir' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-4 h-4" />
                    <span>Mermas / Bajas</span>
                </button>

                <!-- Tab: Reponer a Destinos -->
                <button type="button" wire:click="$set('activeTab', 'reabastecer')"
                    class="flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150 shrink-0 {{ $activeTab === 'reabastecer' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-xs ring-1 ring-black/5 dark:ring-white/10' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                    <x-filament::icon icon="heroicon-m-arrow-up-right" class="w-4 h-4" />
                    <span>Reponer</span>
                </button>
            </div>
        </nav>

        <!-- NAVEGACIÓN VERTICAL DESKTOP (Sidebar lateral fijo) -->
        <aside class="hidden lg:block lg:w-72 xl:w-80 shrink-0 lg:sticky lg:top-24">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-3 shadow-xs space-y-1.5">
                <div class="px-3.5 py-2.5 border-b border-gray-100 dark:border-gray-800 mb-1 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Operaciones
                    </span>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-800">
                        Lavandería
                    </span>
                </div>

                <!-- Botón: Inventario -->
                <button type="button" wire:click="$set('activeTab', 'inventario')"
                    class="w-full flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-left transition-all duration-150 group relative {{ $activeTab === 'inventario' ? 'bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 font-semibold shadow-xs ring-1 ring-primary-500/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/60' }}">
                    @if ($activeTab === 'inventario')
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-primary-500 rounded-r-full"></div>
                    @endif
                    <div class="p-2 rounded-lg {{ $activeTab === 'inventario' ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 group-hover:text-primary-500' }}">
                        <x-filament::icon icon="heroicon-m-archive-box" class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium leading-snug">Inventario Actual</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Blancos y químicos en stock</p>
                    </div>
                </button>

                <!-- Botón: Entrada de Blancos -->
                <button type="button" wire:click="$set('activeTab', 'entrada')"
                    class="w-full flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-left transition-all duration-150 group relative {{ $activeTab === 'entrada' ? 'bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 font-semibold shadow-xs ring-1 ring-primary-500/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/60' }}">
                    @if ($activeTab === 'entrada')
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-primary-500 rounded-r-full"></div>
                    @endif
                    <div class="p-2 rounded-lg {{ $activeTab === 'entrada' ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 group-hover:text-primary-500' }}">
                        <x-filament::icon icon="heroicon-m-arrow-down-tray" class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium leading-snug">Entrada de Blancos</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Recepción de ropa sucia</p>
                    </div>
                </button>

                <!-- Botón: Entrada de Insumos Químicos -->
                <button type="button" wire:click="$set('activeTab', 'entrada_insumos')"
                    class="w-full flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-left transition-all duration-150 group relative {{ $activeTab === 'entrada_insumos' ? 'bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 font-semibold shadow-xs ring-1 ring-primary-500/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/60' }}">
                    @if ($activeTab === 'entrada_insumos')
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-primary-500 rounded-r-full"></div>
                    @endif
                    <div class="p-2 rounded-lg {{ $activeTab === 'entrada_insumos' ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 group-hover:text-primary-500' }}">
                        <x-filament::icon icon="heroicon-m-plus-circle" class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium leading-snug">Entrada de Insumos</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Recepción de químicos y detergente</p>
                    </div>
                </button>

                <!-- Botón: Consumo por Jornada -->
                <button type="button" wire:click="$set('activeTab', 'jornada')"
                    class="w-full flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-left transition-all duration-150 group relative {{ $activeTab === 'jornada' ? 'bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 font-semibold shadow-xs ring-1 ring-primary-500/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/60' }}">
                    @if ($activeTab === 'jornada')
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-primary-500 rounded-r-full"></div>
                    @endif
                    <div class="p-2 rounded-lg {{ $activeTab === 'jornada' ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 group-hover:text-primary-500' }}">
                        <x-filament::icon icon="heroicon-m-sparkles" class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium leading-snug">Consumo por Jornada</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Gasto de químicos por turno</p>
                    </div>
                </button>

                <!-- Botón: Mermas y Bajas -->
                <button type="button" wire:click="$set('activeTab', 'consumir')"
                    class="w-full flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-left transition-all duration-150 group relative {{ $activeTab === 'consumir' ? 'bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 font-semibold shadow-xs ring-1 ring-primary-500/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/60' }}">
                    @if ($activeTab === 'consumir')
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-primary-500 rounded-r-full"></div>
                    @endif
                    <div class="p-2 rounded-lg {{ $activeTab === 'consumir' ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 group-hover:text-primary-500' }}">
                        <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium leading-snug">Mermas y Bajas</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Baja de piezas dañadas</p>
                    </div>
                </button>

                <!-- Botón: Reponer a Destinos -->
                <button type="button" wire:click="$set('activeTab', 'reabastecer')"
                    class="w-full flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-left transition-all duration-150 group relative {{ $activeTab === 'reabastecer' ? 'bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 font-semibold shadow-xs ring-1 ring-primary-500/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/60' }}">
                    @if ($activeTab === 'reabastecer')
                        <div class="absolute left-0 top-2.5 bottom-2.5 w-1 bg-primary-500 rounded-r-full"></div>
                    @endif
                    <div class="p-2 rounded-lg {{ $activeTab === 'reabastecer' ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 group-hover:text-primary-500' }}">
                        <x-filament::icon icon="heroicon-m-arrow-up-right" class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium leading-snug">Reponer a Destinos</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Despacho de ropa limpia</p>
                    </div>
                </button>
            </div>
        </aside>

        <!-- ÁREA PRINCIPAL DE CONTENIDO -->
        <main class="flex-1 min-w-0 w-full">
            @if ($activeTab === 'inventario')
                <div class="space-y-4">
                    {{ $this->table }}
                </div>
            @elseif ($activeTab === 'entrada')
                <form wire:submit.prevent="submitEntrada" class="space-y-6">
                    {{ $this->entradaForm }}

                    <div class="flex justify-end pt-2">
                        <x-filament::button type="submit" color="primary" size="lg" icon="heroicon-m-check">
                            Registrar Entrada de Blancos
                        </x-filament::button>
                    </div>
                </form>
            @elseif ($activeTab === 'entrada_insumos')
                <form wire:submit.prevent="submitEntradaInsumos" class="space-y-6">
                    {{ $this->entradaInsumosForm }}

                    <div class="flex justify-end pt-2">
                        <x-filament::button type="submit" color="primary" size="lg" icon="heroicon-m-check">
                            Registrar Ingreso de Insumos
                        </x-filament::button>
                    </div>
                </form>
            @elseif ($activeTab === 'jornada')
                <form wire:submit.prevent="submitJornada" class="space-y-6">
                    {{ $this->jornadaForm }}

                    <div class="flex justify-end pt-2">
                        <x-filament::button type="submit" color="primary" size="lg" icon="heroicon-m-check">
                            Registrar Consumo de Jornada
                        </x-filament::button>
                    </div>
                </form>
            @elseif ($activeTab === 'consumir')
                <form wire:submit.prevent="submitConsumir" class="space-y-6">
                    {{ $this->consumirForm }}

                    <div class="flex justify-end pt-2">
                        <x-filament::button type="submit" color="primary" size="lg" icon="heroicon-m-check">
                            Registrar Merma / Baja
                        </x-filament::button>
                    </div>
                </form>
            @elseif ($activeTab === 'reabastecer')
                <form wire:submit.prevent="submitReabastecer" class="space-y-6">
                    {{ $this->reabastecerForm }}

                    <div class="flex justify-end pt-2">
                        <x-filament::button type="submit" color="primary" size="lg" icon="heroicon-m-check">
                            Reponer Insumos a Destino
                        </x-filament::button>
                    </div>
                </form>
            @endif
        </main>
    </div>
</x-filament-panels::page>
