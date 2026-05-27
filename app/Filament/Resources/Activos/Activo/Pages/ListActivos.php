<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo\Pages;

use App\Filament\Resources\Activos\Activo\ActivoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListActivos extends ListRecords
{
    protected static string $resource = ActivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo Activo')
                ->icon(Heroicon::Plus),
        ];
    }
}
