<?php

declare(strict_types=1);

namespace App\Filament\Resources\Espacios\Espacio\Pages;

use App\Filament\Resources\Espacios\Espacio\EspacioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEspacios extends ListRecords
{
    protected static string $resource = EspacioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo Espacio'),
        ];
    }
}
