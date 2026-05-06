<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorDocumento\Pages;

use App\Filament\Resources\Colaboradores\ColaboradorDocumento\ColaboradorDocumentoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageColaboradorDocumentos extends ManageRecords
{
    protected static string $resource = ColaboradorDocumentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading('Cargar nuevo documento')
                ->modalWidth('2xl'),
        ];
    }
}
