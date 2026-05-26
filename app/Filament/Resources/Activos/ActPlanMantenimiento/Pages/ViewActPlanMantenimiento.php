<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActPlanMantenimiento\Pages;

use App\Filament\Resources\Activos\ActPlanMantenimiento\ActPlanMantenimientoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewActPlanMantenimiento extends ViewRecord
{
    protected static string $resource = ActPlanMantenimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
