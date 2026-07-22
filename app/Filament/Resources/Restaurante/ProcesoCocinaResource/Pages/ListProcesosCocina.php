<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Pages;

use App\Filament\Resources\Restaurante\ProcesoCocinaResource\ProcesoCocinaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProcesosCocina extends ListRecords
{
    protected static string $resource = ProcesoCocinaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
