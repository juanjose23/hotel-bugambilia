<x-filament-panels::page>
    <form wire:submit.prevent="guardar" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-filament::button type="submit" icon="heroicon-o-beaker">
                Procesar Transformación de Materia Prima
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
