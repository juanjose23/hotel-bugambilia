<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\TurnoResource\Pages;

use App\Filament\Resources\Limpieza\TurnoResource\TurnoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTurno extends ViewRecord
{
    protected static string $resource = TurnoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
