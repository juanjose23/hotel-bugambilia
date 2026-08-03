<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\CargoFacturacionResource\Pages;

use App\Filament\Resources\Cuentas\CargoFacturacionResource\CargoFacturacionResource;
use Filament\Resources\Pages\EditRecord;

final class EditCargoFacturacion extends EditRecord
{
    protected static string $resource = CargoFacturacionResource::class;
}
