<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActPlanMantenimiento\Pages;

use App\Filament\Resources\Activos\ActPlanMantenimiento\ActPlanMantenimientoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActPlanMantenimiento extends CreateRecord
{
    protected static string $resource = ActPlanMantenimientoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
