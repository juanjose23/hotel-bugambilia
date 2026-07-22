<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Pages;

use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLimpiezaEjecucion extends ViewRecord
{
    protected static string $resource = LimpiezaEjecucionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
