<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\TurnoResource\Pages;

use App\Filament\Resources\Limpieza\TurnoResource\TurnoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTurnos extends ListRecords
{
    protected static string $resource = TurnoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
