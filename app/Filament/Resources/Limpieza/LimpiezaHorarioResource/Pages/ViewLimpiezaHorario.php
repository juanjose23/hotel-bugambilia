<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Pages;

use App\Filament\Resources\Limpieza\LimpiezaHorarioResource\LimpiezaHorarioResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLimpiezaHorario extends ViewRecord
{
    protected static string $resource = LimpiezaHorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
