<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PlatoResource\Pages;

use App\Filament\Resources\Restaurante\PlatoResource\PlatoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPlato extends ViewRecord
{
    protected static string $resource = PlatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
