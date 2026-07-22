<?php

namespace App\Filament\Resources\Catalogos\Ubicaciones\Pages;

use App\Filament\Resources\Catalogos\Ubicaciones\UbicacionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewUbicacion extends ViewRecord
{
    protected static string $resource = UbicacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->modalHeading('Actualizar ubicación')->modalDescription('Complete el formulario para actualizar la ubicación.')
                ->modalWidth(Width::FourExtraLarge),
        ];
    }
}
