<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Pages;

use App\Filament\Resources\Limpieza\LimpiezaHorarioResource\LimpiezaHorarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLimpiezaHorarios extends ListRecords
{
    protected static string $resource = LimpiezaHorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
