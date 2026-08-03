<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\CargoFacturacionResource\Pages;

use App\Filament\Resources\Cuentas\CargoFacturacionResource\CargoFacturacionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCargoFacturacion extends CreateRecord
{
    protected static string $resource = CargoFacturacionResource::class;
}
