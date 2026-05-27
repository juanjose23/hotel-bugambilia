<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\PrefijoCodigo\Pages;

use App\Filament\Resources\Activos\PrefijoCodigo\PrefijoCodigoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrefijoCodigos extends ListRecords
{
    protected static string $resource = PrefijoCodigoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo Prefijo'),
        ];
    }
}
