<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaSerieResource\Pages;

use App\Filament\Resources\Facturacion\FacturaSerieResource\FacturaSerieResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListFacturaSeries extends ListRecords
{
    protected static string $resource = FacturaSerieResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
