<?php

namespace App\Filament\Resources\Auditoria\AuditoriaReporte\Pages;

use App\Filament\Resources\Auditoria\AuditoriaReporte\AuditoriaReporteResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditoriaReporte extends ViewRecord
{
    protected static string $resource = AuditoriaReporteResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
