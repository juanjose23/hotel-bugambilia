<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaResource\Pages;

use App\Filament\Resources\Facturacion\FacturaResource\FacturaResource;
use Filament\Resources\Pages\ListRecords;

final class ListFacturas extends ListRecords
{
    protected static string $resource = FacturaResource::class;
}
