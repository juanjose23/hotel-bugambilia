<?php

namespace App\Filament\Resources\Auditoria\AuditoriaReportes\Pages;

use App\Filament\Resources\Auditoria\AuditoriaReportes\AuditoriaReporteResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditoriaReporte extends ViewRecord
{
    protected static string $resource = AuditoriaReporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
