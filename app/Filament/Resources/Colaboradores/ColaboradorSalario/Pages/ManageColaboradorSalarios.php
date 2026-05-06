<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorSalario\Pages;

use App\Filament\Resources\Colaboradores\ColaboradorSalario\ColaboradorSalarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageColaboradorSalarios extends ManageRecords
{
    protected static string $resource = ColaboradorSalarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Registrar nuevo salario')
                ->modalWidth('2xl'),
        ];
    }
}
