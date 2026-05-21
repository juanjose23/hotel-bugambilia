<?php

namespace App\Filament\Resources\Compras\Recepciones\Pages;

use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListRecepciones extends ListRecords
{
    protected static string $resource = RecepcionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Recepción')
                ->icon(Heroicon::Plus),
        ];
    }
}
