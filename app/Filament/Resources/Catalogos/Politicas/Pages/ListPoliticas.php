<?php

namespace App\Filament\Resources\Catalogos\Politicas\Pages;

use App\Filament\Resources\Catalogos\Politicas\PoliticasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPoliticas extends ListRecords
{
    protected static string $resource = PoliticasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
