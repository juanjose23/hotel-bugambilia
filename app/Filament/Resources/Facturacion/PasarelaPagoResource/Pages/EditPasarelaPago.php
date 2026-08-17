<?php

declare(strict_types=1);

namespace App\Filament\Resources\Facturacion\PasarelaPagoResource\Pages;

use App\Filament\Resources\Facturacion\PasarelaPagoResource\PasarelaPagoResource;
use Filament\Resources\Pages\EditRecord;

final class EditPasarelaPago extends EditRecord
{
    protected static string $resource = PasarelaPagoResource::class;
}
