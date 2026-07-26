<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\CuentaResource\Pages;

use App\Filament\Resources\Cuentas\CuentaResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewCuenta extends ViewRecord
{
    protected static string $resource = CuentaResource::class;

    protected static ?string $title = 'Detalle de Cuenta';
}
