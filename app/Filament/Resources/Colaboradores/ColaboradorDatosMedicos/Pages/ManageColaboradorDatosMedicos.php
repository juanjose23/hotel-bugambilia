<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorDatosMedicos\Pages;

use App\Filament\Resources\Colaboradores\ColaboradorDatosMedicos\ColaboradorDatosMedicosResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageColaboradorDatosMedicos extends ManageRecords
{
    protected static string $resource = ColaboradorDatosMedicosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Registrar información médica')
                ->modalWidth('3xl'),
        ];
    }
}
