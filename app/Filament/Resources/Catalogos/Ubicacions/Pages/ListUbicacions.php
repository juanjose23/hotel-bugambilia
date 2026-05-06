<?php

namespace App\Filament\Resources\Catalogos\Ubicacions\Pages;

use App\Filament\Resources\Catalogos\Ubicacions\UbicacionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUbicacions extends ListRecords
{
    protected static string $resource = UbicacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_3d')
                ->label('Vista 3D')
                ->color('info')
                ->icon('heroicon-o-cube')
                ->url(ArbolUbicacion::getUrl()),
            CreateAction::make()->modalHeading('Crear ubicación')->modalSubheading('Complete el formulario para crear una nueva ubicación.')->modalButton('Crear')
                ->modalWidth('4xl'),
        ];
    }
}
