@php use App\Filament\Resources\Colaboradores\Colaboradors\ColaboradorResource; @endphp
<div class="flex items-center gap-3">
    <x-filament::button type="submit" size="sm">
        Guardar
    </x-filament::button>

    <x-filament::button 
        color="gray" 
        tag="a" 
        :href="ColaboradorResource::getUrl('index')" 
        size="sm"
        outline
    >
        Cancelar
    </x-filament::button>
</div>
