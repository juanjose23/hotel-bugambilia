<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Tabs Nav -->
        <div class="flex border-b border-gray-200 dark:border-gray-800">
            <button wire:click="$set('activeTab', 'inventario')"
                class="py-3 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'inventario' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                Inventario en Lavandería
            </button>
            <button wire:click="$set('activeTab', 'consumir')"
                class="py-3 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'consumir' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                Registrar Consumo / Merma
            </button>
            <button wire:click="$set('activeTab', 'reabastecer')"
                class="py-3 px-6 text-sm font-semibold border-b-2 transition-all duration-200 {{ $activeTab === 'reabastecer' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                Reponer a Habitaciones / Ubicación
            </button>
        </div>

        <!-- Content -->
        @if ($activeTab === 'inventario')
            <div class="space-y-4">
                {{ $this->table }}
            </div>
        @elseif ($activeTab === 'consumir')
            <form wire:submit.prevent="submitConsumir" class="space-y-6">
                {{ $this->consumirForm }}

                <div class="flex justify-end">
                    <x-filament::button type="submit" color="primary">
                        Registrar Consumo / Merma
                    </x-filament::button>
                </div>
            </form>
        @elseif ($activeTab === 'reabastecer')
            <form wire:submit.prevent="submitReabastecer" class="space-y-6">
                {{ $this->reabastecerForm }}

                <div class="flex justify-end">
                    <x-filament::button type="submit" color="primary">
                        Reponer Insumos
                    </x-filament::button>
                </div>
            </form>
        @endif
    </div>
</x-filament-panels::page>
