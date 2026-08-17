<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaSerieResource\Pages;

use App\Filament\Resources\Facturacion\FacturaSerieResource\FacturaSerieResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateFacturaSerie extends CreateRecord
{
    protected static string $resource = FacturaSerieResource::class;
}
