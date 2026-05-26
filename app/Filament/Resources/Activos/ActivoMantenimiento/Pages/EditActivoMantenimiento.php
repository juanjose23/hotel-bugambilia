<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoMantenimiento\Pages;

use App\Filament\Resources\Activos\ActivoMantenimiento\ActivoMantenimientoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditActivoMantenimiento extends EditRecord
{
    protected static string $resource = ActivoMantenimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
