<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoMantenimiento\Pages;

use App\Filament\Resources\Activos\ActivoMantenimiento\ActivoMantenimientoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateActivoMantenimiento extends CreateRecord
{
    protected static string $resource = ActivoMantenimientoResource::class;
}
