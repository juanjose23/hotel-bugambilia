<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorCargoHistorial\Pages;

use App\Filament\Resources\Colaboradores\ColaboradorCargoHistorial\ColaboradorCargoHistorialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageColaboradorCargoHistorials extends ManageRecords
{
    protected static string $resource = ColaboradorCargoHistorialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Registrar nuevo cargo')
                ->modalWidth('3xl'),
        ];
    }
}
