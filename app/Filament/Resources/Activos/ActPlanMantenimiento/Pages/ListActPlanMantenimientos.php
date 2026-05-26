<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActPlanMantenimiento\Pages;

use App\Filament\Resources\Activos\ActPlanMantenimiento\ActPlanMantenimientoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActPlanMantenimientos extends ListRecords
{
    protected static string $resource = ActPlanMantenimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
