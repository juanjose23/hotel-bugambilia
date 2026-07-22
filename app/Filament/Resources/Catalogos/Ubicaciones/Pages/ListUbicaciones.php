<?php

namespace App\Filament\Resources\Catalogos\Ubicaciones\Pages;

use App\Filament\Resources\Catalogos\Ubicaciones\UbicacionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class ListUbicaciones extends ListRecords
{
    protected static string $resource = UbicacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_3d')
                ->label('Grafico de ubicaciones')
                ->color('info')
                ->icon(Heroicon::MapPin)
                ->url(ArbolUbicacion::getUrl()),
            CreateAction::make()->modalHeading('Crear ubicación')->modalDescription('Complete el formulario para crear una nueva ubicación.')
                ->modalWidth(Width::FourExtraLarge),
        ];
    }
}
