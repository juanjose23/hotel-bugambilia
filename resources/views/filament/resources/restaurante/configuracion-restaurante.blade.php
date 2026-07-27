<x-filament-panels::page>
    <form wire:submit="guardar" class="space-y-6">
        
        <x-filament::section icon="heroicon-o-printer">
            <x-slot name="heading">Configuración de Impresoras Térmicas por Área</x-slot>
            <x-slot name="description">Establezca los identificadores de impresora de tickets para cada estación de servicio.</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Impresora Cocina Principal</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" wire:model="impresoraCocina" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Impresora Barra / Bar</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" wire:model="impresoraBar" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Impresora Postres & Repostería</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" wire:model="impresoraPostres" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Impresora Parrilla & Carnes</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" wire:model="impresoraParrilla" />
                    </x-filament::input.wrapper>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section icon="heroicon-o-calculator">
            <x-slot name="heading">Configuración de Impuestos y Propinas</x-slot>
            <x-slot name="description">Defina los porcentajes aplicables al cierre de cuentas.</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Porcentaje Impuesto (IVA %)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" step="0.01" wire:model="impuestoPorcentaje" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Porcentaje Propina Sugerida (%)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" step="0.01" wire:model="propinaSugerida" />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-1">Copias por Comanda</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" min="1" max="5" wire:model="copiasTicket" />
                    </x-filament::input.wrapper>
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="impresionAutomatica" class="rounded border-gray-300 text-[#6b003e] focus:ring-[#6b003e]" />
                        Habilitar Impresión Automática al Enviar Comanda
                    </label>
                </div>
            </div>
        </x-filament::section>

        <div class="flex justify-end">
            <x-filament::button type="submit" color="primary" icon="heroicon-o-check">
                Guardar Configuración POS
            </x-filament::button>
        </div>

    </form>
</x-filament-panels::page>
