<div class="flex items-center gap-3">
    <x-filament::button type="submit" size="sm">
        Guardar
    </x-filament::button>

    <x-filament::button 
        color="gray" 
        tag="a" 
        :href="\App\Filament\Resources\Colaboradores\Colaboradors\ColaboradorResource::getUrl('index')" 
        size="sm"
        outline
    >
        Cancelar
    </x-filament::button>
</div>
