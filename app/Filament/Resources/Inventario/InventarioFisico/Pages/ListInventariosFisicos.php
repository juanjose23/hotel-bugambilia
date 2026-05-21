<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\InventarioFisico\Pages;

use App\Filament\Resources\Inventario\InventarioFisico\InventarioFisicoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListInventariosFisicos extends ListRecords
{
    protected static string $resource = InventarioFisicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva Toma Física')
                ->icon(Heroicon::Plus),
        ];
    }
}
