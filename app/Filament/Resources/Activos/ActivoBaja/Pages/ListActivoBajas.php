<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoBaja\Pages;

use App\Filament\Resources\Activos\ActivoBaja\ActivoBajaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListActivoBajas extends ListRecords
{
    protected static string $resource = ActivoBajaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nueva Acta de Baja'),
        ];
    }
}
