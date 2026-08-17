<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaFolioResource\Pages;

use App\Filament\Resources\Facturacion\FacturaFolioResource\FacturaFolioResource;
use Filament\Resources\Pages\ListRecords;

final class ListFacturaFolios extends ListRecords
{
    protected static string $resource = FacturaFolioResource::class;
}
