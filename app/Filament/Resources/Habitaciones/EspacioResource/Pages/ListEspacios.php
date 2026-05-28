<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\Pages;

use App\Filament\Resources\Habitaciones\EspacioResource\EspacioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListEspacios extends ListRecords
{
    protected static string $resource = EspacioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Registrar Espacio')
                ->icon(Heroicon::Plus),
        ];
    }
}
