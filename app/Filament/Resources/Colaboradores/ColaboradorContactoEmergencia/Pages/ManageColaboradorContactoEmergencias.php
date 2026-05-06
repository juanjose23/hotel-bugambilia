<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia\Pages;

use App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia\ColaboradorContactoEmergenciaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageColaboradorContactoEmergencias extends ManageRecords
{
    protected static string $resource = ColaboradorContactoEmergenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Registrar contacto de emergencia')
                ->modalWidth('3xl'),
        ];
    }
}
