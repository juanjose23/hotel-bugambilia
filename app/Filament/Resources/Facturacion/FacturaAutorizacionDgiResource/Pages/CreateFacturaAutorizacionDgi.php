<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\FacturaAutorizacionDgiResource\Pages;

use App\Filament\Resources\Facturacion\FacturaAutorizacionDgiResource\FacturaAutorizacionDgiResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateFacturaAutorizacionDgi extends CreateRecord
{
    protected static string $resource = FacturaAutorizacionDgiResource::class;
}
