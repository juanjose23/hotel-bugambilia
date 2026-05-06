<?php

namespace App\Filament\Resources\Colaboradores\Colaboradors\Pages;

use App\Filament\Resources\Colaboradores\Colaboradors\ColaboradorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListColaboradors extends ListRecords
{
    protected static string $resource = ColaboradorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}