<?php

namespace App\Filament\Resources\Catalogos\Pais\Pages;

use App\Filament\Resources\Catalogos\Pais\PaisResource;
use App\UseCases\Pais\CrearPais;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPais extends ListRecords
{
    protected static string $resource = PaisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(fn(array $data) => app(CrearPais::class)->execute($data)),
        ];
    }
}