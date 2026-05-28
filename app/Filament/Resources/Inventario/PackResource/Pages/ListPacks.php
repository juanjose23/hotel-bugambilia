<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\PackResource\Pages;

use App\Filament\Resources\Inventario\PackResource\PackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPacks extends ListRecords
{
    protected static string $resource = PackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo Pack')
                ->icon(Heroicon::Plus),
        ];
    }
}
