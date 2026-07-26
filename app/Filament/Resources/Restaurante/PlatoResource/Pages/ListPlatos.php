<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PlatoResource\Pages;

use App\Filament\Resources\Restaurante\PlatoResource\PlatoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListPlatos extends ListRecords
{
    protected static string $resource = PlatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
