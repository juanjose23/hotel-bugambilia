<?php

namespace App\Filament\Resources\Auditoria\AuditoriaReportes\Pages;

use App\Filament\Resources\Auditoria\AuditoriaReportes\AuditoriaReporteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAuditoriaReportes extends ListRecords
{
    protected static string $resource = AuditoriaReporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
