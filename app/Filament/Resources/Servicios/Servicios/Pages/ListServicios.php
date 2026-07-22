<?php

declare(strict_types=1);

namespace App\Filament\Resources\Servicios\Servicios\Pages;

use App\Filament\Resources\Servicios\Servicios\ServicioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServicios extends ListRecords
{
    protected static string $resource = ServicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
