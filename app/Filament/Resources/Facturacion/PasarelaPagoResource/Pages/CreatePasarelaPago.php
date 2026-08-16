<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\PasarelaPagoResource\Pages;

use App\Filament\Resources\Facturacion\PasarelaPagoResource\PasarelaPagoResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePasarelaPago extends CreateRecord
{
    protected static string $resource = PasarelaPagoResource::class;
}
