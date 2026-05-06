<?php

namespace App\Filament\Resources\Catalogos\Ubicacions\Pages;

use App\Filament\Resources\Catalogos\Ubicacions\UbicacionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUbicacion extends ViewRecord
{
    protected static string $resource = UbicacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->modalHeading('Actualizar ubicación')->modalSubheading('Complete el formulario para actualizar la ubicación.')->modalButton('Actualizar')->livewireClickHandlerEnabled(false)
                ->modalWidth('4xl'),
        ];
    }
}
