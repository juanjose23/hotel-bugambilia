<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaSerieResource\Pages;

use App\Filament\Resources\Facturacion\FacturaSerieResource\FacturaSerieResource;
use Filament\Resources\Pages\EditRecord;

final class EditFacturaSerie extends EditRecord
{
    protected static string $resource = FacturaSerieResource::class;
}
